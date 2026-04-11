<?php

namespace App\Services\Meetings;

use App\Models\LiveSession;
use App\Models\LiveSessionAttendance;
use App\Models\Student;
use Illuminate\Support\Carbon;

class LiveSessionAttendanceService
{
    public function refreshPresence(LiveSession $liveSession): void
    {
        $timeoutSeconds = config('meetings.heartbeat_timeout_seconds', 45);
        $cutoff = Carbon::now(config('app.timezone', 'UTC'))->subSeconds($timeoutSeconds);

        LiveSessionAttendance::query()
            ->where('live_session_id', $liveSession->id)
            ->where('is_present', true)
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $cutoff);
            })
            ->get()
            ->each(function (LiveSessionAttendance $attendance) use ($cutoff) {
                $attendance->forceFill([
                    'is_present' => false,
                    'last_left_at' => $attendance->last_seen_at ?? $cutoff,
                ])->save();
            });
    }

    public function markStudentHeartbeat(LiveSession $liveSession, Student $student, ?string $participantId = null): LiveSessionAttendance
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        $attendance = LiveSessionAttendance::query()->firstOrNew([
            'live_session_id' => $liveSession->id,
            'student_id' => $student->id,
        ]);

        if (! $attendance->exists) {
            $attendance->first_joined_at = $now;
            $attendance->join_count = 1;
        } elseif (! $attendance->is_present) {
            $attendance->join_count = ((int) $attendance->join_count) + 1;
        }

        $attendance->is_present = true;
        $attendance->last_seen_at = $now;
        if ($participantId) {
            $attendance->jitsi_participant_id = $participantId;
        }

        $attendance->save();

        return $attendance->fresh();
    }
}
