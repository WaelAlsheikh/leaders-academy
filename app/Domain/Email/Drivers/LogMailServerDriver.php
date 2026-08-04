<?php

namespace App\Domain\Email\Drivers;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\DTOs\MailboxDTO;
use Illuminate\Support\Facades\Log;

/**
 * Development / dry-run driver — logs operations without touching Mail DB.
 */
class LogMailServerDriver implements MailServerDriver
{
    public function createMailbox(MailboxDTO $mailbox): void
    {
        Log::channel('single')->info('[mail.driver.log] createMailbox', $mailbox->toArray());
    }

    public function deleteMailbox(string $email): void
    {
        Log::info('[mail.driver.log] deleteMailbox', compact('email'));
    }

    public function disableMailbox(string $email): void
    {
        Log::info('[mail.driver.log] disableMailbox', compact('email'));
    }

    public function enableMailbox(string $email): void
    {
        Log::info('[mail.driver.log] enableMailbox', compact('email'));
    }

    public function renameMailbox(string $fromEmail, string $toEmail): void
    {
        Log::info('[mail.driver.log] renameMailbox', compact('fromEmail', 'toEmail'));
    }

    public function changePassword(string $email, string $plainPassword): void
    {
        Log::info('[mail.driver.log] changePassword', ['email' => $email]);
    }

    public function changeQuota(string $email, int $quotaMb): void
    {
        Log::info('[mail.driver.log] changeQuota', compact('email', 'quotaMb'));
    }

    public function createAlias(string $sourceEmail, string $destinationEmail): void
    {
        Log::info('[mail.driver.log] createAlias', compact('sourceEmail', 'destinationEmail'));
    }

    public function removeAlias(string $sourceEmail): void
    {
        Log::info('[mail.driver.log] removeAlias', compact('sourceEmail'));
    }

    public function createDistributionList(string $address, array $memberEmails = []): void
    {
        Log::info('[mail.driver.log] createDistributionList', compact('address', 'memberEmails'));
    }

    public function removeDistributionList(string $address): void
    {
        Log::info('[mail.driver.log] removeDistributionList', compact('address'));
    }

    public function syncListMembers(string $address, array $memberEmails): void
    {
        Log::info('[mail.driver.log] syncListMembers', compact('address', 'memberEmails'));
    }

    public function syncUser(MailboxDTO $mailbox): void
    {
        $this->createMailbox($mailbox);
    }

    public function healthCheck(): array
    {
        return [
            'ok' => true,
            'driver' => 'log',
            'details' => ['mode' => 'dry-run'],
        ];
    }
}
