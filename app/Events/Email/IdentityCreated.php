<?php

namespace App\Events\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IdentityCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Model $identity,
    ) {}
}
