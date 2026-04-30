<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectMaterial extends Model
{
    protected $fillable = [
        'doctor_id',
        'registrable_subject_id',
        'material_type',
        'title',
        'description',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registrableSubject(): BelongsTo
    {
        return $this->belongsTo(RegistrableSubject::class, 'registrable_subject_id');
    }
}
