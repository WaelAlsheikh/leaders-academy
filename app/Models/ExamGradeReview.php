<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamGradeReview extends Model
{
    protected $fillable = [
        'grade_id',
        'reviewer_doctor_id',
        'reviewer_user_id',
        'action',
        'notes',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(ExamGrade::class, 'grade_id');
    }

    public function reviewerDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewer_doctor_id');
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
