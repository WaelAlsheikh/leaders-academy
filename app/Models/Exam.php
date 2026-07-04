<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'registrable_subject_id',
        'class_section_id',
        'doctor_id',
        'semester_id',
        'creation_mode',
        'question_count',
        'category_ids',
        'question_types_filter',
        'total_points',
        'status',
        'exam_date',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'allow_late_entry',
        'questions_locked',
        'created_by',
        'approved_at',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'total_points' => 'decimal:2',
        'category_ids' => 'array',
        'question_types_filter' => 'array',
        'allow_late_entry' => 'boolean',
        'questions_locked' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function registrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(ExamQuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ExamGrade::class);
    }

    public function hasEssayQuestions(): bool
    {
        return $this->quizQuestions()->where('type_snapshot', 'essay')->exists();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft'], true) && ! $this->questions_locked;
    }
}
