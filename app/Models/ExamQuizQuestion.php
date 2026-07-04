<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuizQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question_id',
        'sort_order',
        'points',
        'question_text_snapshot',
        'type_snapshot',
    ];

    protected $casts = [
        'points' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(ExamQuizQuestionChoice::class)->orderBy('sort_order');
    }

    public function isAutoGradable(): bool
    {
        return in_array($this->type_snapshot, ['single_choice', 'multiple_choice'], true);
    }
}
