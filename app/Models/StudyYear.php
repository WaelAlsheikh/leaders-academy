<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyYear extends Model
{
    protected $fillable = [
        'registrable_entity_id',
        'name',
        'sort_order',
    ];

    public function registrableEntity(): BelongsTo
    {
        return $this->belongsTo(RegistrableEntity::class);
    }

    public function studyTerms(): HasMany
    {
        return $this->hasMany(StudyTerm::class)->orderBy('sort_order')->orderBy('id');
    }

    public function registrableSubjects(): HasMany
    {
        return $this->hasManyThrough(
            RegistrableSubject::class,
            StudyTerm::class,
            'study_year_id',
            'study_term_id',
            'id',
            'id'
        );
    }
}
