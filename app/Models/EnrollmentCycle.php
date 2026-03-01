<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class EnrollmentCycle extends Model
{
    protected $fillable = [
        'college_id',
        'name',
        'registration_starts_at',
        'registration_ends_at',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'registration_starts_at' => 'datetime',
        'registration_ends_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'enrollment_cycle_subject')
            ->withPivot(['is_open'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function semester(): HasOne
    {
        return $this->hasOne(Semester::class);
    }

    public function isOpenNow(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }
        $now = Carbon::now();
        if ($this->registration_starts_at && $now->lt($this->registration_starts_at)) {
            return false;
        }
        if ($this->registration_ends_at && $now->gt($this->registration_ends_at)) {
            return false;
        }
        return true;
    }
}
