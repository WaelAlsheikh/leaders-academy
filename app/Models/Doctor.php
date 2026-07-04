<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Authenticatable
{
    protected $table = 'doctors';
    protected $guard = 'doctor';

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'academic_degree',
        'specialization',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teachingSubjects(): HasMany
    {
        return $this->hasMany(RegistrableSubject::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function subjectMaterials(): HasMany
    {
        return $this->hasMany(SubjectMaterial::class);
    }

    public function examQuestionCategories(): HasMany
    {
        return $this->hasMany(ExamQuestionCategory::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
