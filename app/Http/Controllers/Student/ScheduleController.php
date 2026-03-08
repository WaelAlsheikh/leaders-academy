<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $sections = $student->sections()
            ->wherePivot('status', 'active')
            ->with(['subject', 'registrableSubject', 'semester', 'meetings'])
            ->get();

        $timezone = config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $startOfWeek = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $schedule = [];
        foreach ($sections as $section) {
            foreach ($section->meetings as $meeting) {
                $meetingDate = $startOfWeek->copy()->addDays((int) $meeting->day_of_week);
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $meetingDate->toDateString() . ' ' . $meeting->starts_at, $timezone);
                $endDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $meetingDate->toDateString() . ' ' . $meeting->ends_at, $timezone);

                if ($meeting->start_date && $meetingDate->lt(Carbon::parse($meeting->start_date, $timezone))) {
                    continue;
                }
                if ($meeting->end_date && $meetingDate->gt(Carbon::parse($meeting->end_date, $timezone))) {
                    continue;
                }

                $isNow = $now->between($startDateTime, $endDateTime);
                $canJoin = $now->between($startDateTime->copy()->subMinutes(5), $endDateTime);

                $schedule[] = [
                    'day_of_week' => (int) $meeting->day_of_week,
                    'date' => $meetingDate->toDateString(),
                    'starts_at' => $meeting->starts_at,
                    'ends_at' => $meeting->ends_at,
                    'is_now' => $isNow,
                    'can_join' => $canJoin,
                    'section' => $section,
                ];
            }
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

        return view('student.schedule.index', compact('schedule', 'dayNames'));
    }
}
