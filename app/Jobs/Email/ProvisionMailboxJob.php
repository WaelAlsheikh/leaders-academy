<?php

namespace App\Jobs\Email;

use App\Domain\Email\Services\MailboxProvisioningService;
use App\Notifications\MailboxCredentialsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProvisionMailboxJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $identityClass,
        public readonly int $identityId,
    ) {}

    public function handle(MailboxProvisioningService $provisioning): void
    {
        $identity = $this->identityClass::query()->find($this->identityId);
        if (! $identity) {
            return;
        }

        try {
            $result = $provisioning->provisionForIdentity($identity);
            $this->notifyCredentials($identity, $result);
        } catch (\Throwable $e) {
            Log::error('ProvisionMailboxJob failed', [
                'identity' => $this->identityClass,
                'id' => $this->identityId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @param  array{account: \App\Domain\Email\Models\MailAccount, plain_password: string|null}  $result
     */
    private function notifyCredentials(object $identity, array $result): void
    {
        $password = $result['plain_password'] ?? null;
        $account = $result['account'] ?? null;
        $personal = $identity->email ?? null;

        if (! $password || ! $account || ! $personal) {
            return;
        }

        Notification::route('mail', $personal)->notify(new MailboxCredentialsNotification(
            institutionalEmail: $account->institutional_email,
            plainPassword: $password,
            webmailUrl: (string) config('email_module.webmail_url'),
        ));
    }
}
