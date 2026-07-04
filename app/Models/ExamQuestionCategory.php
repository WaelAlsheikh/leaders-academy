<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'registrable_subject_id',
        'parent_id',
        'name',
        'sort_order',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'category_id');
    }
}
