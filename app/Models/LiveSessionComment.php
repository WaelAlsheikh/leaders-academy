<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionComment extends Model
{
    protected $fillable = [
        'live_session_id',
        'author_type',
        'author_id',
        'author_name_snapshot',
        'body',
        'is_hidden',
        'hidden_at',
        'hidden_by_doctor_id',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'hidden_at' => 'datetime',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function hiddenByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'hidden_by_doctor_id');
    }
}
