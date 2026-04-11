<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Services\Meetings\LiveSessionManager;
use App\Services\Meetings\MeetingOccurrenceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly MeetingOccurrenceService $occurrenceService,
        private readonly LiveSessionManager $liveSessionManager,
    ) {
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $sections = $student->sections()
            ->wherePivot('status', 'active')
            ->with(['subject', 'registrableSubject', 'doctor', 'semester.enrollmentCycle.archiveRecord', 'meetings'])
            ->get();

        $timezone = config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $startOfWeek = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SATURDAY);
        $allMeetings = $sections->flatMap->meetings;
        $occurrences = $this->occurrenceService->occurrencesForRange($allMeetings, $startOfWeek, $endOfWeek, $timezone);
        $meetingIds = $occurrences->map(fn (array $item) => $item['section_meeting']->id)->unique()->values()->all();
        $occurrenceDates = $occurrences->pluck('occurrence_date')->unique()->values()->all();

        $liveSessions = LiveSession::query()
            ->whereIn('section_meeting_id', $meetingIds)
            ->whereIn('occurrence_date', $occurrenceDates)
            ->get()
            ->keyBy(fn (LiveSession $session) => $session->section_meeting_id . '|' . $session->occurrence_date->toDateString());

        $schedule = [];
        $hasArchivedCycles = false;
        foreach ($sections as $section) {
            if ($section->semester?->enrollmentCycle?->is_archived) {
                $hasArchivedCycles = true;
            }
        }

        foreach ($occurrences as $occurrence) {
            $section = $occurrence['section'];
            $meeting = $occurrence['section_meeting'];
            $liveSession = $liveSessions->get($meeting->id . '|' . $occurrence['occurrence_date']);
            $status = $this->liveSessionManager->scheduleStatusData(
                $liveSession,
                $occurrence['scheduled_starts_at']->copy()->startOfDay(),
                $occurrence['scheduled_starts_at'],
                $occurrence['scheduled_ends_at'],
                $now
            );
            $canJoin = $liveSession
                && $status['code'] === 'started'
                && $liveSession->canStudentEnter();

            $schedule[] = [
                'day_of_week' => (int) $meeting->day_of_week,
                'date' => $occurrence['occurrence_date'],
                'starts_at' => $occurrence['scheduled_starts_at']->format('H:i:s'),
                'ends_at' => $occurrence['scheduled_ends_at']->format('H:i:s'),
                'is_now' => $now->between($occurrence['scheduled_starts_at'], $occurrence['scheduled_ends_at']),
                'can_join' => $canJoin,
                'section' => $section,
                'section_meeting' => $meeting,
                'live_session' => $liveSession,
                'status' => $status,
            ];
        }

        usort($schedule, function ($a, $b) {
            if ($a['day_of_week'] === $b['day_of_week']) {
                return strcmp($a['starts_at'], $b['starts_at']);
            }
            return $a['day_of_week'] <=> $b['day_of_week'];
        });

        $dayNames = [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];

        return view('student.schedule.index', compact('schedule', 'dayNames', 'hasArchivedCycles'));
    }
}
