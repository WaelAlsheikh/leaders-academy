<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionAttendance extends Model
{
    protected $fillable = [
        'live_session_id',
        'student_id',
        'first_joined_at',
        'last_seen_at',
        'last_left_at',
        'is_present',
        'join_count',
        'jitsi_participant_id',
    ];

    protected $casts = [
        'first_joined_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_left_at' => 'datetime',
        'is_present' => 'boolean',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
