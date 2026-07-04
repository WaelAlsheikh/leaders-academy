<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'registrable_subject_id',
        'category_id',
        'type',
        'question_text',
        'default_points',
        'difficulty',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'default_points' => 'decimal:2',
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionCategory::class, 'category_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(ExamQuestionChoice::class, 'question_id')->orderBy('sort_order');
    }

    public function isAutoGradable(): bool
    {
        return in_array($this->type, ['single_choice', 'multiple_choice'], true);
    }

    public function requiresManualGrading(): bool
    {
        return $this->type === 'essay';
    }
}
