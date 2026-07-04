<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuizQuestionChoice extends Model
{
    protected $fillable = [
        'exam_quiz_question_id',
        'choice_id',
        'choice_text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function quizQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuizQuestion::class, 'exam_quiz_question_id');
    }

    public function sourceChoice(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionChoice::class, 'choice_id');
    }
}
