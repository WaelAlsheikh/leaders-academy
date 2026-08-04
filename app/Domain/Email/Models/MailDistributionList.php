<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailDistributionList extends Model
{
    protected $fillable = [
        'address',
        'name',
        'sync_rule',
        'is_auto_synced',
        'is_active',
    ];

    protected $casts = [
        'sync_rule' => 'array',
        'is_auto_synced' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(MailDistributionListMember::class, 'list_id');
    }
}
