<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    protected $fillable = [
        'semester_id',
        'subject_id',
        'name',
        'mode',
        'zoom_url',
        'notes',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(SectionMeeting::class, 'section_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_sections', 'section_id', 'student_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }
}
