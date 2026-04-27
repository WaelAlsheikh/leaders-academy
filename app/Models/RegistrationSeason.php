<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class RegistrationSeason extends Model
{
    protected $fillable = [
        'name',
        'code',
        'registration_starts_at',
        'registration_ends_at',
        'status',
        'created_by',
        'archived_by',
        'archived_at',
    ];

    protected $casts = [
        'registration_starts_at' => 'datetime',
        'registration_ends_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function enrollmentCycles(): HasMany
    {
        return $this->hasMany(EnrollmentCycle::class)->orderBy('id');
    }

    public function enabledEnrollmentCycles(): HasMany
    {
        return $this->hasMany(EnrollmentCycle::class)
            ->where('is_enabled', true)
            ->orderBy('id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function scopeActiveListing(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchivedListing(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeOpenListing(Builder $query): Builder
    {
        return $query->activeListing()->where('status', 'open');
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_at !== null;
    }

    public function isOpenNow(): bool
    {
        if ($this->is_archived || $this->status !== 'open') {
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
