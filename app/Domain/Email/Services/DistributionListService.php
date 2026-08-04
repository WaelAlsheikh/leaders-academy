<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\Enums\IdentityType;
use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailDistributionList;
use App\Domain\Email\Models\MailDistributionListMember;

class DistributionListService
{
    public function __construct(
        private readonly MailServerDriver $driver,
        private readonly EmailAuditService $audit,
    ) {}

    public function create(string $address, string $name, ?array $syncRule = null, bool $autoSync = false): MailDistributionList
    {
        $list = MailDistributionList::query()->updateOrCreate(
            ['address' => strtolower($address)],
            [
                'name' => $name,
                'sync_rule' => $syncRule,
                'is_auto_synced' => $autoSync,
                'is_active' => true,
            ]
        );

        $this->driver->createDistributionList($list->address, []);
        $this->audit->log('list.created', null, ['address' => $list->address]);

        return $list;
    }

    public function sync(MailDistributionList $list): int
    {
        $rule = $list->sync_rule ?? [];
        $emails = $this->resolveEmailsFromRule($rule);

        $list->members()->delete();
        foreach ($emails as $email) {
            $accountId = MailAccount::query()->where('institutional_email', $email)->value('id');
            MailDistributionListMember::query()->create([
                'list_id' => $list->id,
                'mail_account_id' => $accountId,
                'external_email' => $accountId ? null : $email,
            ]);
        }

        $this->driver->syncListMembers($list->address, $emails);
        $this->audit->log('list.synced', null, ['address' => $list->address, 'count' => count($emails)]);

        return count($emails);
    }

    public function syncAllAuto(): int
    {
        $total = 0;
        MailDistributionList::query()
            ->where('is_active', true)
            ->where('is_auto_synced', true)
            ->each(function (MailDistributionList $list) use (&$total) {
                $total += $this->sync($list);
            });

        return $total;
    }

    /**
     * @param  array<string, mixed>  $rule
     * @return list<string>
     */
    private function resolveEmailsFromRule(array $rule): array
    {
        if (empty($rule['identity_type'])) {
            return [];
        }

        $type = IdentityType::from($rule['identity_type']);
        $accounts = MailAccount::query()
            ->with('mailable')
            ->where('identity_type', $type)
            ->where('status', 'active')
            ->get();

        if (! empty($rule['college_id']) || ! empty($rule['section_id'])) {
            $collegeId = isset($rule['college_id']) ? (int) $rule['college_id'] : null;
            $sectionId = isset($rule['section_id']) ? (int) $rule['section_id'] : null;

            $accounts = $accounts->filter(function (MailAccount $account) use ($collegeId, $sectionId) {
                $mailable = $account->mailable;
                if (! $mailable) {
                    return false;
                }
                if ($collegeId !== null && isset($mailable->college_id) && (int) $mailable->college_id !== $collegeId) {
                    return false;
                }
                if ($sectionId !== null && isset($mailable->section_id) && (int) $mailable->section_id !== $sectionId) {
                    return false;
                }

                return true;
            });
        }

        return $accounts->pluck('institutional_email')->unique()->values()->all();
    }
}
