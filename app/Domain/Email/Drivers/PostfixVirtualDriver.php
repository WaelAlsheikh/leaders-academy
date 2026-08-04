<?php

namespace App\Domain\Email\Drivers;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\DTOs\MailboxDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Writes to Postfix/Dovecot virtual-mailbox tables on the `mailserver` connection.
 *
 * Expected Mail DB tables (see docs/email/mailserver-schema.sql):
 * - virtual_domains (id, name)
 * - virtual_users (id, domain_id, email, password, quota_mb, active, maildir)
 * - virtual_aliases (id, domain_id, source, destination, active)
 */
class PostfixVirtualDriver implements MailServerDriver
{
    private function db()
    {
        return DB::connection('mailserver');
    }

    public function createMailbox(MailboxDTO $mailbox): void
    {
        $domainId = $this->ensureDomain($mailbox->domain);
        $password = $mailbox->plainPassword ?? Str::password(16);
        $hash = $this->hashPassword($password);
        $maildir = $mailbox->domain.'/'.$mailbox->localPart.'/';

        $this->db()->table('virtual_users')->updateOrInsert(
            ['email' => $mailbox->email],
            [
                'domain_id' => $domainId,
                'password' => $hash,
                'quota_mb' => $mailbox->quotaMb,
                'active' => $mailbox->enabled ? 1 : 0,
                'maildir' => $maildir,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function deleteMailbox(string $email): void
    {
        $this->db()->table('virtual_users')->where('email', $email)->delete();
        $this->db()->table('virtual_aliases')->where('destination', $email)->delete();
    }

    public function disableMailbox(string $email): void
    {
        $this->db()->table('virtual_users')->where('email', $email)->update(['active' => 0, 'updated_at' => now()]);
    }

    public function enableMailbox(string $email): void
    {
        $this->db()->table('virtual_users')->where('email', $email)->update(['active' => 1, 'updated_at' => now()]);
    }

    public function renameMailbox(string $fromEmail, string $toEmail): void
    {
        $user = $this->db()->table('virtual_users')->where('email', $fromEmail)->first();
        if (! $user) {
            return;
        }

        $local = Str::before($toEmail, '@');
        $domain = Str::after($toEmail, '@');
        $domainId = $this->ensureDomain($domain);

        $this->db()->table('virtual_users')->where('email', $fromEmail)->update([
            'email' => $toEmail,
            'domain_id' => $domainId,
            'maildir' => $domain.'/'.$local.'/',
            'updated_at' => now(),
        ]);

        $this->createAlias($fromEmail, $toEmail);
    }

    public function changePassword(string $email, string $plainPassword): void
    {
        $this->db()->table('virtual_users')->where('email', $email)->update([
            'password' => $this->hashPassword($plainPassword),
            'updated_at' => now(),
        ]);
    }

    public function changeQuota(string $email, int $quotaMb): void
    {
        $this->db()->table('virtual_users')->where('email', $email)->update([
            'quota_mb' => $quotaMb,
            'updated_at' => now(),
        ]);
    }

    public function createAlias(string $sourceEmail, string $destinationEmail): void
    {
        $domain = Str::after($sourceEmail, '@');
        $domainId = $this->ensureDomain($domain);

        $this->db()->table('virtual_aliases')->updateOrInsert(
            ['source' => $sourceEmail],
            [
                'domain_id' => $domainId,
                'destination' => $destinationEmail,
                'active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function removeAlias(string $sourceEmail): void
    {
        $this->db()->table('virtual_aliases')->where('source', $sourceEmail)->delete();
    }

    public function createDistributionList(string $address, array $memberEmails = []): void
    {
        foreach ($memberEmails as $member) {
            $this->createAlias($address, $member);
        }
    }

    public function removeDistributionList(string $address): void
    {
        $this->db()->table('virtual_aliases')->where('source', $address)->delete();
    }

    public function syncListMembers(string $address, array $memberEmails): void
    {
        $this->removeDistributionList($address);
        $this->createDistributionList($address, $memberEmails);
    }

    public function syncUser(MailboxDTO $mailbox): void
    {
        $this->createMailbox($mailbox);
    }

    public function healthCheck(): array
    {
        try {
            $this->db()->select('select 1');
            $users = $this->db()->table('virtual_users')->count();

            return [
                'ok' => true,
                'driver' => 'postfix_virtual',
                'details' => ['virtual_users' => $users],
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'driver' => 'postfix_virtual',
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    private function ensureDomain(string $domain): int
    {
        $row = $this->db()->table('virtual_domains')->where('name', $domain)->first();
        if ($row) {
            return (int) $row->id;
        }

        return (int) $this->db()->table('virtual_domains')->insertGetId([
            'name' => $domain,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function hashPassword(string $plain): string
    {
        // Dovecot-compatible BLF-CRYPT via PHP password_hash (bcrypt)
        return password_hash($plain, PASSWORD_BCRYPT);
    }
}
