<?php

namespace App\Domain\Email\Concerns;

use App\Domain\Email\Models\MailAccount;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasInstitutionalMail
{
    public function mailAccount(): MorphOne
    {
        return $this->morphOne(MailAccount::class, 'mailable');
    }

    public function preferredNotificationEmail(): ?string
    {
        if (config('email_module.notification_prefer_institutional')) {
            $institutional = $this->institutional_email ?: $this->mailAccount?->institutional_email;
            if ($institutional && $this->mailAccount?->isActive()) {
                return $institutional;
            }
        }

        return $this->email ?? null;
    }

    public function routeNotificationForMail($notification = null): ?string
    {
        return $this->preferredNotificationEmail();
    }
}
