<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailDistributionListMember extends Model
{
    protected $fillable = [
        'list_id',
        'mail_account_id',
        'external_email',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(MailDistributionList::class, 'list_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class, 'mail_account_id');
    }
}
