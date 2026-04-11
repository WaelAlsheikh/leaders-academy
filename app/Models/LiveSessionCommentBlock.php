<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionCommentBlock extends Model
{
    protected $fillable = [
        'live_session_id',
        'student_id',
        'is_blocked',
        'blocked_at',
        'blocked_by_doctor_id',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function blockedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'blocked_by_doctor_id');
    }
}
