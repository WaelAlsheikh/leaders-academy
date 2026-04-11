<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class LiveSession extends Model
{
    protected $fillable = [
        'section_id',
        'section_meeting_id',
        'occurrence_date',
        'meeting_provider',
        'provider_room_name',
        'provider_payload',
        'scheduled_starts_at',
        'scheduled_ends_at',
        'started_at',
        'started_by_doctor_id',
        'entry_closed_at',
        'entry_closed_by_doctor_id',
        'ended_at',
        'ended_by_doctor_id',
        'comments_enabled',
        'audio_moderation_enabled',
        'video_moderation_enabled',
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'provider_payload' => 'array',
        'scheduled_starts_at' => 'datetime',
        'scheduled_ends_at' => 'datetime',
        'started_at' => 'datetime',
        'entry_closed_at' => 'datetime',
        'ended_at' => 'datetime',
        'comments_enabled' => 'boolean',
        'audio_moderation_enabled' => 'boolean',
        'video_moderation_enabled' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'section_id');
    }

    public function sectionMeeting(): BelongsTo
    {
        return $this->belongsTo(SectionMeeting::class, 'section_meeting_id');
    }

    public function startedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'started_by_doctor_id');
    }

    public function entryClosedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'entry_closed_by_doctor_id');
    }

    public function endedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'ended_by_doctor_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(LiveSessionAttendance::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LiveSessionComment::class)->orderBy('id');
    }

    public function commentBlocks(): HasMany
    {
        return $this->hasMany(LiveSessionCommentBlock::class);
    }

    public function lifecycleStatus(): string
    {
        if ($this->ended_at) {
            return 'ended';
        }

        if ($this->started_at && $this->entry_closed_at) {
            return 'entry_closed';
        }

        if ($this->started_at) {
            return 'started';
        }

        return 'not_started';
    }

    public function canStudentEnter(): bool
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        return $this->started_at !== null
            && $this->isDoctorReady()
            && $this->entry_closed_at === null
            && $this->ended_at === null
            && ($this->scheduled_ends_at === null || $now->lessThanOrEqualTo($this->scheduled_ends_at));
    }

    public function isDoctorReady(): bool
    {
        return (bool) data_get($this->provider_payload, 'host_presence.is_ready', false);
    }

    public function isExpired(?Carbon $now = null): bool
    {
        $now ??= Carbon::now(config('app.timezone', 'UTC'));

        return $this->scheduled_ends_at !== null
            && $now->greaterThan($this->scheduled_ends_at->copy()->addMinutes(30));
    }
}
