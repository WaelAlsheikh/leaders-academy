<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'registrable_subject_id',
        'class_section_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isOpenForSubmission(?\DateTimeInterface $at = null): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $now = $at ? \Carbon\Carbon::instance($at) : now();

        return $now->gte($this->starts_at) && $now->lte($this->ends_at);
    }

    public function windowStatus(): string
    {
        if (! in_array($this->status, ['published', 'closed'], true)) {
            return 'unavailable';
        }

        $now = now();

        if ($now->lt($this->starts_at)) {
            return 'upcoming';
        }

        if ($now->gt($this->ends_at) || $this->status === 'closed') {
            return 'closed';
        }

        return 'open';
    }

    public function windowStatusLabel(): string
    {
        return config('assignments.window_labels')[$this->windowStatus()] ?? $this->windowStatus();
    }

    public function statusLabel(): string
    {
        return config('assignments.statuses')[$this->status] ?? $this->status;
    }
}
