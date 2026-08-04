<?php

namespace App\Domain\Email\Models;

use App\Domain\Email\Enums\IdentityType;
use Illuminate\Database\Eloquent\Model;

class MailQuotaPolicy extends Model
{
    protected $table = 'mail_quotas_policies';

    protected $fillable = [
        'identity_type',
        'quota_mb',
        'max_aliases',
    ];

    protected $casts = [
        'quota_mb' => 'integer',
        'max_aliases' => 'integer',
        'identity_type' => IdentityType::class,
    ];
}
