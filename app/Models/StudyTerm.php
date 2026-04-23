<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyTerm extends Model
{
    protected $fillable = [
        'study_year_id',
        'name',
        'code',
        'sort_order',
    ];

    public function studyYear(): BelongsTo
    {
        return $this->belongsTo(StudyYear::class);
    }

    public function registrableSubjects(): HasMany
    {
        return $this->hasMany(RegistrableSubject::class)->orderBy('name');
    }

    public function legacySubjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('name');
    }

    public function getDisplayTitleAttribute(): string
    {
        return trim($this->studyYear?->name.' - '.$this->name, ' -');
    }

    public function getRegistrableEntityAttribute(): ?RegistrableEntity
    {
        return $this->studyYear?->registrableEntity;
    }
}
