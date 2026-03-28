<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();

        $sections = ClassSection::query()
            ->where('doctor_id', $doctor->id)
            ->with([
                'doctor',
                'registrableSubject.registrableEntity',
                'semester.enrollmentCycle.registrableEntity',
                'semester.enrollmentCycle.archiveRecord',
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

        return view('doctor.dashboard', compact(
            'doctor',
            'subjectGroups',
            'subjectCount',
            'sectionCount',
            'studentCount'
        ));
    }
}
