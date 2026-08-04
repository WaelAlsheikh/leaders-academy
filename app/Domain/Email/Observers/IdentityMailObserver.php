<?php

namespace App\Domain\Email\Observers;

use App\Events\Email\IdentityCreated;
use Illuminate\Database\Eloquent\Model;

class IdentityMailObserver
{
    public function created(Model $model): void
    {
        IdentityCreated::dispatch($model);
    }
}
