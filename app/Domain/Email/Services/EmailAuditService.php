<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailAuditLog;
use Illuminate\Support\Facades\Auth;

class EmailAuditService
{
    public function log(string $action, ?MailAccount $account = null, array $payload = [], ?object $actor = null): void
    {
        $actor = $actor ?? Auth::user();

        MailAuditLog::query()->create([
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'mail_account_id' => $account?->id,
            'payload' => $payload ?: null,
            'ip' => request()?->ip(),
        ]);
    }
}
