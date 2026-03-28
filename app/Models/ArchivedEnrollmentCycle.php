<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedEnrollmentCycle extends Model
{
    protected $fillable = [
        'enrollment_cycle_id',
        'archived_by',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function enrollmentCycle(): BelongsTo
    {
        return $this->belongsTo(EnrollmentCycle::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
