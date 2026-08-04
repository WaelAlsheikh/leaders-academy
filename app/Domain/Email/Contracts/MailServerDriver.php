<?php

namespace App\Domain\Email\Contracts;

use App\Domain\Email\DTOs\MailboxDTO;

interface MailServerDriver
{
    public function createMailbox(MailboxDTO $mailbox): void;

    public function deleteMailbox(string $email): void;

    public function disableMailbox(string $email): void;

    public function enableMailbox(string $email): void;

    public function renameMailbox(string $fromEmail, string $toEmail): void;

    public function changePassword(string $email, string $plainPassword): void;

    public function changeQuota(string $email, int $quotaMb): void;

    public function createAlias(string $sourceEmail, string $destinationEmail): void;

    public function removeAlias(string $sourceEmail): void;

    public function createDistributionList(string $address, array $memberEmails = []): void;

    public function removeDistributionList(string $address): void;

    public function syncListMembers(string $address, array $memberEmails): void;

    public function syncUser(MailboxDTO $mailbox): void;

    /**
     * @return array{ok: bool, driver: string, details: array<string, mixed>}
     */
    public function healthCheck(): array;
}
