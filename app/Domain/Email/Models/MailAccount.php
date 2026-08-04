<?php

namespace App\Domain\Email\Models;

use App\Domain\Email\Enums\IdentityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MailAccount extends Model
{
    protected $fillable = [
        'mailable_type',
        'mailable_id',
        'identity_type',
        'domain_id',
        'local_part',
        'institutional_email',
        'status',
        'quota_mb',
        'used_bytes',
        'provisioning_status',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'quota_mb' => 'integer',
        'used_bytes' => 'integer',
        'last_synced_at' => 'datetime',
        'identity_type' => IdentityType::class,
    ];

    public function mailable(): MorphTo
    {
        return $this->morphTo();
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(MailDomain::class, 'domain_id');
    }

    public function mailbox(): HasOne
    {
        return $this->hasOne(MailMailbox::class, 'mail_account_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(MailAlias::class, 'mail_account_id');
    }

    public function forwards(): HasMany
    {
        return $this->hasMany(MailForward::class, 'mail_account_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSynced(): bool
    {
        return $this->provisioning_status === 'synced';
    }
}
