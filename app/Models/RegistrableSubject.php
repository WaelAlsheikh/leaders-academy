<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrableSubject extends Model
{
    protected $fillable = [
        'registrable_entity_id',
        'legacy_subject_id',
        'name',
        'code',
        'credit_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function registrableEntity(): BelongsTo
    {
        return $this->belongsTo(RegistrableEntity::class);
    }

    public function legacySubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'legacy_subject_id');
    }

    public function enrollmentCycles(): BelongsToMany
    {
        return $this->belongsToMany(EnrollmentCycle::class, 'enrollment_cycle_registrable_subject')
            ->withPivot(['is_open'])
            ->withTimestamps();
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'registration_registrable_subject')
            ->withPivot(['credit_hours', 'price_per_hour', 'total_price'])
            ->withTimestamps();
    }

    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'semester_subject', 'registrable_subject_id', 'semester_id')
            ->withPivot(['is_active', 'registered_count', 'subject_id'])
            ->withTimestamps();
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'registrable_subject_id');
    }
}

