<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'student_id',
        'college_id',
        'registrable_entity_id',
        'enrollment_cycle_id',
        'semester_id',
        'status',
        'subjects_count',
        'total_hours',
        'subtotal_amount',
        'registration_fee',
        'total_amount',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function registrableEntity()
    {
        return $this->belongsTo(RegistrableEntity::class);
    }

    public function enrollmentCycle()
    {
        return $this->belongsTo(EnrollmentCycle::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'registration_subject')
            ->withPivot(['credit_hours', 'price_per_hour', 'total_price']);
    }

    public function registrableSubjects()
    {
        return $this->belongsToMany(RegistrableSubject::class, 'registration_registrable_subject')
            ->withPivot(['credit_hours', 'price_per_hour', 'total_price'])
            ->withTimestamps();
    }
}
