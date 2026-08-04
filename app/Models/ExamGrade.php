<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamGrade extends Model
{
    protected $fillable = [
        'exam_id',
        'attempt_id',
        'student_id',
        'raw_score',
        'max_score',
        'percentage',
        'status',
        'reviewed_by_doctor_id',
        'approved_by',
        'published_at',
    ];

    protected $casts = [
        'raw_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by_doctor_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ExamGradeReview::class, 'grade_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function passThreshold(): int
    {
        return ExamSetting::passPercentage();
    }

    public function isPassed(): bool
    {
        if ($this->percentage === null) {
            return false;
        }

        return (float) $this->percentage >= $this->passThreshold();
    }

    public function resultLabel(): string
    {
        return $this->isPassed() ? 'ناجح' : 'راسب';
    }

    public function resultCssModifier(): string
    {
        return $this->isPassed() ? 'pass' : 'fail';
    }
}
