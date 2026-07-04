<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'exam_quiz_question_id',
        'question_id',
        'answer_text',
        'selected_choice_id',
        'selected_choice_ids',
        'is_correct',
        'points_awarded',
        'graded_by_doctor_id',
        'graded_at',
        'feedback',
    ];

    protected $casts = [
        'selected_choice_ids' => 'array',
        'is_correct' => 'boolean',
        'points_awarded' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function quizQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuizQuestion::class, 'exam_quiz_question_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    public function gradedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'graded_by_doctor_id');
    }
}
