<?php

namespace App\Http\Controllers;

use App\Models\College;
use Illuminate\Support\Carbon;

class CollegeScheduleController extends Controller
{
    public function show(College $college)
    {
        $semesters = $college->semesters()
            ->where('status', 'active')
            ->with(['classSections.subject', 'classSections.meetings'])
            ->get();

        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $schedule = [];
        foreach ($semesters as $semester) {
            foreach ($semester->classSections as $section) {
                foreach ($section->meetings as $meeting) {
                    $meetingDate = $startOfWeek->copy()->addDays((int) $meeting->day_of_week);
                    $startDateTime = Carbon::parse($meetingDate->toDateString() . ' ' . $meeting->starts_at);
                    $endDateTime = Carbon::parse($meetingDate->toDateString() . ' ' . $meeting->ends_at);

                    if ($meeting->start_date && $meetingDate->lt($meeting->start_date)) {
                        continue;
                    }
                    if ($meeting->end_date && $meetingDate->gt($meeting->end_date)) {
                        continue;
                    }

                    $schedule[] = [
                        'day_of_week' => (int) $meeting->day_of_week,
                        'date' => $meetingDate->toDateString(),
                        'starts_at' => $meeting->starts_at,
                        'ends_at' => $meeting->ends_at,
                        'section' => $section,
                        'semester' => $semester,
                    ];
                }
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

        return view('colleges.schedule', compact('college', 'schedule', 'dayNames'));
    }
}
