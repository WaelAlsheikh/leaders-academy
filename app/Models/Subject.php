<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'college_id',
        'name',
        'code',
        'credit_hours',
        'is_active',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function enrollmentCycles()
    {
        return $this->belongsToMany(EnrollmentCycle::class, 'enrollment_cycle_subject')
            ->withPivot(['is_open'])
            ->withTimestamps();
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'semester_subject')
            ->withPivot(['is_active', 'registered_count'])
            ->withTimestamps();
    }

    public function classSections()
    {
        return $this->hasMany(ClassSection::class);
    }

    public function registrableSubjects()
    {
        return $this->hasMany(RegistrableSubject::class, 'legacy_subject_id');
    }
}
