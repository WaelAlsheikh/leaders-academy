<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\DTOs\MailboxDTO;
use App\Domain\Email\Enums\IdentityType;
use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailAuditLog;
use App\Domain\Email\Models\MailDomain;
use App\Domain\Email\Models\MailMailbox;
use App\Domain\Email\Models\MailProvisioningJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MailboxProvisioningService
{
    public function __construct(
        private readonly MailServerDriver $driver,
        private readonly EmailAddressGenerator $generator,
        private readonly EmailAuditService $audit,
    ) {}

    /**
     * @return array{account: MailAccount, plain_password: string|null}
     */
    public function provisionForIdentity(Model $identity, bool $forceNewPassword = true): array
    {
        $type = IdentityType::fromModel($identity);
        $domainName = $this->generator->domainName();
        $domain = MailDomain::query()->firstOrCreate(
            ['name' => $domainName],
            ['is_active' => true]
        );

        $existing = MailAccount::query()
            ->where('mailable_type', $identity->getMorphClass())
            ->where('mailable_id', $identity->getKey())
            ->first();

        if ($existing && $existing->isSynced() && $existing->isActive()) {
            return ['account' => $existing, 'plain_password' => null];
        }

        $email = $existing?->institutional_email ?: $this->generator->generate($identity, $type);
        $localPart = $this->generator->localPartFromEmail($email);
        $plainPassword = $forceNewPassword ? Str::password((int) config('email_module.password_length', 16)) : null;
        $quota = $type->defaultQuotaMb();

        $job = MailProvisioningJob::query()->create([
            'type' => 'provision_mailbox',
            'payload' => ['email' => $email, 'identity_type' => $type->value],
            'status' => 'running',
            'attempts' => 1,
        ]);

        try {
            $account = DB::transaction(function () use ($identity, $type, $domain, $email, $localPart, $quota, $existing) {
                $account = $existing ?? new MailAccount;
                $account->fill([
                    'mailable_type' => $identity->getMorphClass(),
                    'mailable_id' => $identity->getKey(),
                    'identity_type' => $type,
                    'domain_id' => $domain->id,
                    'local_part' => $localPart,
                    'institutional_email' => $email,
                    'status' => 'pending',
                    'quota_mb' => $quota,
                    'provisioning_status' => 'pending',
                    'last_error' => null,
                ]);
                $account->save();

                if (in_array('institutional_email', $identity->getFillable(), true)) {
                    $identity->forceFill(['institutional_email' => $email])->save();
                }

                MailMailbox::query()->updateOrCreate(
                    ['mail_account_id' => $account->id],
                    [
                        'maildir_path' => $domain->name.'/'.$localPart.'/',
                        'enabled' => true,
                        'password_hash_algo' => 'BLF-CRYPT',
                    ]
                );

                return $account;
            });

            $dto = new MailboxDTO(
                email: $email,
                localPart: $localPart,
                domain: $domain->name,
                identityType: $type,
                quotaMb: $quota,
                plainPassword: $plainPassword,
                enabled: true,
                name: $this->displayName($identity),
            );

            $this->driver->createMailbox($dto);

            $account->forceFill([
                'status' => 'active',
                'provisioning_status' => 'synced',
                'last_synced_at' => now(),
                'last_error' => null,
            ])->save();

            $job->forceFill(['status' => 'completed', 'error' => null])->save();

            $this->audit->log('mailbox.provisioned', $account, [
                'email' => $email,
                'driver' => config('email_module.driver'),
            ]);

            return ['account' => $account->fresh(), 'plain_password' => $plainPassword];
        } catch (\Throwable $e) {
            if (isset($account)) {
                $account->forceFill([
                    'provisioning_status' => 'failed',
                    'last_error' => $e->getMessage(),
                ])->save();
            }

            $job->forceFill([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    public function disable(MailAccount $account): void
    {
        $this->driver->disableMailbox($account->institutional_email);
        $account->forceFill(['status' => 'disabled'])->save();
        $account->mailbox?->forceFill(['enabled' => false])->save();
        $this->audit->log('mailbox.disabled', $account);
    }

    public function enable(MailAccount $account): void
    {
        $this->driver->enableMailbox($account->institutional_email);
        $account->forceFill(['status' => 'active'])->save();
        $account->mailbox?->forceFill(['enabled' => true])->save();
        $this->audit->log('mailbox.enabled', $account);
    }

    public function resetPassword(MailAccount $account): string
    {
        $password = Str::password((int) config('email_module.password_length', 16));
        $this->driver->changePassword($account->institutional_email, $password);
        $this->audit->log('mailbox.password_reset', $account);

        return $password;
    }

    public function changeQuota(MailAccount $account, int $quotaMb): void
    {
        $this->driver->changeQuota($account->institutional_email, $quotaMb);
        $account->forceFill(['quota_mb' => $quotaMb])->save();
        $this->audit->log('mailbox.quota_changed', $account, ['quota_mb' => $quotaMb]);
    }

    public function healthCheck(): array
    {
        return $this->driver->healthCheck();
    }

    private function displayName(Model $identity): string
    {
        return (string) ($identity->full_name
            ?? $identity->name
            ?? trim(($identity->first_name ?? '').' '.($identity->last_name ?? ''))
            ?: 'User');
    }
}
