<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailMailbox extends Model
{
    protected $fillable = [
        'mail_account_id',
        'maildir_path',
        'uid',
        'gid',
        'enabled',
        'password_hash_algo',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'uid' => 'integer',
        'gid' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class, 'mail_account_id');
    }
}
