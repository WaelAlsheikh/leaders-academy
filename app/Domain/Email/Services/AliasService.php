<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailAlias;

class AliasService
{
    public function __construct(
        private readonly MailServerDriver $driver,
        private readonly EmailAuditService $audit,
    ) {}

    public function createForAccount(MailAccount $account, string $sourceEmail, string $type = 'user'): MailAlias
    {
        $destination = $account->institutional_email;
        $this->driver->createAlias($sourceEmail, $destination);

        $alias = MailAlias::query()->updateOrCreate(
            ['source_email' => strtolower($sourceEmail)],
            [
                'destination_email' => $destination,
                'mail_account_id' => $account->id,
                'type' => $type,
                'is_active' => true,
            ]
        );

        $this->audit->log('alias.created', $account, ['source' => $sourceEmail]);

        return $alias;
    }

    public function remove(MailAlias $alias): void
    {
        $source = $alias->source_email;
        $account = $alias->account;
        $this->driver->removeAlias($source);
        $alias->delete();
        $this->audit->log('alias.removed', $account, ['source' => $source]);
    }
}
