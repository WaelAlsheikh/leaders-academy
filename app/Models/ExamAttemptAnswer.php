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

    public function isChoiceSelected(int $choiceId): bool
    {
        $type = $this->quizQuestion?->type_snapshot;

        if ($type === 'single_choice') {
            return (int) $this->selected_choice_id === $choiceId;
        }

        if ($type === 'multiple_choice') {
            return in_array($choiceId, $this->selected_choice_ids ?? [], true);
        }

        return false;
    }

    public function hasAnySelection(): bool
    {
        $type = $this->quizQuestion?->type_snapshot;

        if ($type === 'single_choice') {
            return $this->selected_choice_id !== null;
        }

        if ($type === 'multiple_choice') {
            return ! empty($this->selected_choice_ids);
        }

        if ($type === 'essay') {
            return filled(trim((string) $this->answer_text));
        }

        return false;
    }
}
