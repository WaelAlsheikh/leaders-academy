<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailForward extends Model
{
    protected $fillable = [
        'mail_account_id',
        'forward_to',
        'keep_copy',
        'is_active',
    ];

    protected $casts = [
        'keep_copy' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class, 'mail_account_id');
    }
}
