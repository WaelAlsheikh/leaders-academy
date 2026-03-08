<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $fillable = [
        'college_id',
        'enrollment_cycle_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function enrollmentCycle(): BelongsTo
    {
        return $this->belongsTo(EnrollmentCycle::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'semester_subject')
            ->withPivot(['is_active', 'registered_count'])
            ->withTimestamps();
    }

    public function registrableSubjects(): BelongsToMany
    {
        return $this->belongsToMany(RegistrableSubject::class, 'semester_subject', 'semester_id', 'registrable_subject_id')
            ->withPivot(['is_active', 'registered_count', 'subject_id'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }
}
