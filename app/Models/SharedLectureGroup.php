<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SharedLectureGroup extends Model
{
    protected $fillable = [
        'key',
        'name',
        'host_registrable_subject_id',
        'host_section_id',
    ];

    public function hostRegistrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class, 'host_registrable_subject_id');
    }

    public function hostSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'host_section_id');
    }

    public function registrableSubjects(): BelongsToMany
    {
        return $this->belongsToMany(
            RegistrableSubject::class,
            'shared_lecture_group_subjects',
            'shared_lecture_group_id',
            'registrable_subject_id'
        )->withTimestamps();
    }
}
