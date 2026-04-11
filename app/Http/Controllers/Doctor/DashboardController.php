<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\LiveSession;
use App\Models\SectionMeeting;
use App\Services\Meetings\LiveSessionManager;
use App\Services\Meetings\MeetingOccurrenceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly MeetingOccurrenceService $occurrenceService,
        private readonly LiveSessionManager $liveSessionManager,
    ) {
    }

    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        $timezone = config('app.timezone', 'UTC');

        $sections = ClassSection::query()
            ->where('doctor_id', $doctor->id)
            ->with([
                'doctor',
                'registrableSubject.registrableEntity',
                'semester.enrollmentCycle.registrableEntity',
                'semester.enrollmentCycle.archiveRecord',
                'meetings',
            ])
            ->withCount('students')
            ->orderByDesc('semester_id')
            ->orderBy('registrable_subject_id')
            ->orderBy('name')
            ->get();

        $subjectGroups = $sections
            ->groupBy(fn (ClassSection $section) => (string) ($section->registrable_subject_id ?? 'section-' . $section->id))
            ->map(function ($group) {
                $firstSection = $group->first();

                return [
                    'subject' => $firstSection->registrableSubject,
                    'sections' => $group,
                ];
            })
            ->values();

        $subjectCount = $subjectGroups->count();
        $sectionCount = $sections->count();
        $studentCount = $sections->sum('students_count');

        $rangeStart = Carbon::now($timezone)->startOfDay();
        $rangeEnd = Carbon::now($timezone)->addDays(6)->endOfDay();

        $sectionMeetings = SectionMeeting::query()
            ->whereHas('section', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id)
                    ->where('mode', 'online');
            })
            ->with([
                'section' => function ($query) {
                    $query->withCount('students')
                        ->with([
                            'doctor',
                            'registrableSubject',
                            'semester.enrollmentCycle.registrableEntity',
                            'semester.enrollmentCycle.archiveRecord',
                        ]);
                },
            ])
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        $occurrences = $this->occurrenceService->occurrencesForRange($sectionMeetings, $rangeStart, $rangeEnd, $timezone);
        $occurrenceDates = $occurrences->pluck('occurrence_date')->unique()->values()->all();
        $meetingIds = $occurrences->pluck('section_meeting.id')->unique()->values()->all();

        $liveSessions = LiveSession::query()
            ->whereIn('section_meeting_id', $meetingIds)
            ->whereIn('occurrence_date', $occurrenceDates)
            ->get()
            ->keyBy(fn (LiveSession $session) => $session->section_meeting_id . '|' . $session->occurrence_date->toDateString());

        $upcomingSessions = $occurrences->map(function (array $occurrence) use ($liveSessions, $timezone) {
            $liveSession = $liveSessions->get($occurrence['section_meeting']->id . '|' . $occurrence['occurrence_date']);
            $status = $this->liveSessionManager->statusData($liveSession);
            $scheduledStartsAt = $occurrence['scheduled_starts_at']->copy()->setTimezone($timezone);

            return [
                'section' => $occurrence['section'],
                'section_meeting' => $occurrence['section_meeting'],
                'occurrence_date' => $occurrence['occurrence_date'],
                'scheduled_starts_at' => $scheduledStartsAt,
                'scheduled_ends_at' => $occurrence['scheduled_ends_at']->copy()->setTimezone($timezone),
                'live_session' => $liveSession,
                'status' => $status,
                'can_start_today' => $scheduledStartsAt->toDateString() === Carbon::now($timezone)->toDateString(),
            ];
        })->groupBy(fn (array $item) => $item['scheduled_starts_at']->toDateString());

        return view('doctor.dashboard', compact(
            'doctor',
            'subjectGroups',
            'subjectCount',
            'sectionCount',
            'studentCount',
            'upcomingSessions'
        ));
    }
}
