<?php

namespace App\Listeners\Email;

use App\Events\Email\IdentityCreated;
use App\Jobs\Email\ProvisionMailboxJob;

class ProvisionInstitutionalEmail
{
    public function handle(IdentityCreated $event): void
    {
        if (! config('email_module.provision_on_create', true)) {
            return;
        }

        ProvisionMailboxJob::dispatch(
            $event->identity::class,
            (int) $event->identity->getKey()
        );
    }
}
