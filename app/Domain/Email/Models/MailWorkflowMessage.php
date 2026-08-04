<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MailWorkflowMessage extends Model
{
    protected $fillable = [
        'thread_key',
        'related_type',
        'related_id',
        'mail_account_id',
        'direction',
        'from_email',
        'to_email',
        'subject',
        'message_id',
        'in_reply_to',
        'headers',
        'body_excerpt',
        'status',
    ];

    protected $casts = [
        'headers' => 'array',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class, 'mail_account_id');
    }
}
