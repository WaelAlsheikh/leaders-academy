<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use App\Domain\Email\Concerns\HasInstitutionalMail;

class Student extends Authenticatable
{
    use Notifiable;
    use HasInstitutionalMail;

    protected $guard = 'student';

    protected $fillable = [
        'first_name',
        'last_name',
        'first_name_en',
        'username',
        'email',
        'institutional_email',
        'phone',
        'is_active',
        'password',
        'acceptance_number',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

/**
 * عرض جميع الطلاب (افتراضي)
 */
public function scopeAllStudents($query)
{
    return $query;
}

/**
 * الطلاب النشطون
 */
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

/**
 * الطلاب غير النشطين
 */
public function scopeInactive($query)
{
    return $query->where('is_active', false);
}

    public function sections()
    {
        return $this->belongsToMany(ClassSection::class, 'student_sections', 'student_id', 'section_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function liveSessionAttendances(): HasMany
    {
        return $this->hasMany(LiveSessionAttendance::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function examGrades(): HasMany
    {
        return $this->hasMany(ExamGrade::class);
    }
}

