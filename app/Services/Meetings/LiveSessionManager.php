<?php

namespace App\Services\Meetings;

use App\Models\Doctor;
use App\Models\LiveSession;
use App\Models\SectionMeeting;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LiveSessionManager
{
    public function __construct(
        private readonly MeetingOccurrenceService $occurrenceService,
        private readonly MeetingProviderManager $providerManager,
    ) {
    }

    public function getForOccurrence(SectionMeeting $meeting, string|Carbon $occurrenceDate): ?LiveSession
    {
        $occurrence = $this->occurrenceService->occurrenceForDate($meeting, $occurrenceDate);

        return LiveSession::query()
            ->where('section_meeting_id', $meeting->id)
            ->whereDate('occurrence_date', $occurrence['occurrence_date'])
            ->first();
    }

    public function startForOccurrence(SectionMeeting $meeting, string|Carbon $occurrenceDate, Doctor $doctor): LiveSession
    {
        $occurrence = $this->occurrenceService->occurrenceForDate($meeting, $occurrenceDate);
        $existing = $this->getForOccurrence($meeting, $occurrence['occurrence_date']);

        if ($existing && $existing->ended_at) {
            throw ValidationException::withMessages([
                'meeting' => 'تم إنهاء هذه الجلسة مسبقاً ولا يمكن إعادة فتحها.',
            ]);
        }

        $liveSession = LiveSession::query()->firstOrCreate(
            [
                'section_meeting_id' => $meeting->id,
                'occurrence_date' => $occurrence['occurrence_date'],
            ],
            [
                'section_id' => $meeting->section_id,
                'meeting_provider' => config('meetings.default_provider', 'jitsi_public'),
                'scheduled_starts_at' => $occurrence['scheduled_starts_at'],
                'scheduled_ends_at' => $occurrence['scheduled_ends_at'],
                'comments_enabled' => true,
                'audio_moderation_enabled' => false,
                'video_moderation_enabled' => false,
            ]
        );

        if (! $liveSession->started_at) {
            $liveSession->forceFill([
                'started_at' => Carbon::now(config('app.timezone', 'UTC')),
                'started_by_doctor_id' => $doctor->id,
                'ended_at' => null,
                'ended_by_doctor_id' => null,
            ])->save();
        }

        return $this->providerManager
            ->for($liveSession->meeting_provider)
            ->provisionSession($liveSession);
    }

    public function closeEntry(LiveSession $liveSession, Doctor $doctor): LiveSession
    {
        if (! $liveSession->started_at || $liveSession->ended_at) {
            return $liveSession;
        }

        $liveSession->forceFill([
            'entry_closed_at' => Carbon::now(config('app.timezone', 'UTC')),
            'entry_closed_by_doctor_id' => $doctor->id,
        ])->save();

        return $liveSession->fresh();
    }

    public function reopenEntry(LiveSession $liveSession): LiveSession
    {
        if ($liveSession->ended_at) {
            throw ValidationException::withMessages([
                'meeting' => 'لا يمكن إعادة فتح الدخول بعد إنهاء الجلسة.',
            ]);
        }

        $liveSession->forceFill([
            'entry_closed_at' => null,
            'entry_closed_by_doctor_id' => null,
        ])->save();

        return $liveSession->fresh();
    }

    public function end(LiveSession $liveSession, Doctor $doctor): LiveSession
    {
        if ($liveSession->ended_at) {
            return $liveSession;
        }

        $liveSession->forceFill([
            'entry_closed_at' => $liveSession->entry_closed_at ?? Carbon::now(config('app.timezone', 'UTC')),
            'entry_closed_by_doctor_id' => $liveSession->entry_closed_by_doctor_id ?? $doctor->id,
            'ended_at' => Carbon::now(config('app.timezone', 'UTC')),
            'ended_by_doctor_id' => $doctor->id,
        ])->save();

        return $liveSession->fresh();
    }

    public function statusData(?LiveSession $liveSession): array
    {
        if (! $liveSession) {
            return [
                'code' => 'not_started',
                'label' => 'لم تبدأ الجلسة',
            ];
        }

        return match ($liveSession->lifecycleStatus()) {
            'started' => [
                'code' => 'started',
                'label' => 'بدأت الجلسة',
            ],
            'entry_closed' => [
                'code' => 'entry_closed',
                'label' => 'توقفت إمكانية الدخول لهذه الجلسة',
            ],
            'ended' => [
                'code' => 'ended',
                'label' => 'انتهت الجلسة',
            ],
            default => [
                'code' => 'not_started',
                'label' => 'لم تبدأ الجلسة',
            ],
        };
    }

    public function scheduleStatusData(
        ?LiveSession $liveSession,
        Carbon $occurrenceDate,
        Carbon $scheduledStartsAt,
        Carbon $scheduledEndsAt,
        ?Carbon $now = null
    ): array {
        $now ??= Carbon::now(config('app.timezone', 'UTC'));
        $today = $now->toDateString();
        $occurrenceDay = $occurrenceDate->toDateString();

        if ($occurrenceDay !== $today) {
            return [
                'code' => 'not_started',
                'label' => 'لم تبدأ الجلسة',
            ];
        }

        if (! $liveSession) {
            return [
                'code' => 'not_started',
                'label' => 'لم تبدأ الجلسة',
            ];
        }

        if ($liveSession->ended_at || $now->greaterThan($scheduledEndsAt)) {
            return [
                'code' => 'ended',
                'label' => 'انتهت الجلسة',
            ];
        }

        if ($liveSession->started_at && ! $liveSession->isDoctorReady()) {
            return [
                'code' => 'waiting_for_host',
                'label' => 'بانتظار اعتماد الدكتور للجلسة.',
            ];
        }

        if ($liveSession->started_at && $liveSession->entry_closed_at) {
            return [
                'code' => 'entry_closed',
                'label' => 'توقفت إمكانية الدخول لهذه الجلسة',
            ];
        }

        if ($liveSession->started_at) {
            return [
                'code' => 'started',
                'label' => 'بدأت الجلسة',
            ];
        }

        return [
            'code' => 'not_started',
            'label' => 'لم تبدأ الجلسة',
        ];
    }
}
