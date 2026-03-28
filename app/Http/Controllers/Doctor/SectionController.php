<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    public function show(ClassSection $section)
    {
        $doctor = Auth::guard('doctor')->user();

        $section->load([
            'doctor',
            'registrableSubject.registrableEntity',
            'semester.enrollmentCycle.registrableEntity',
            'semester.enrollmentCycle.archiveRecord',
            'meetings',
            'students',
        ]);

        abort_unless($section->doctor_id === $doctor->id, 403);

        return view('doctor.sections.show', compact('doctor', 'section'));
    }

    public function updateNextLink(Request $request, ClassSection $section)
    {
        $doctor = Auth::guard('doctor')->user();

        $section->loadMissing('semester.enrollmentCycle.archiveRecord');

        abort_unless($section->doctor_id === $doctor->id, 403);

        if ($section->semester?->enrollmentCycle?->is_archived) {
            return back()->withErrors([
                'zoom_url' => 'لا يمكن تعديل رابط شعبة ضمن دورة مؤرشفة.',
            ]);
        }

        $data = $request->validate([
            'zoom_url' => 'nullable|url',
        ]);

        $section->update([
            'zoom_url' => $data['zoom_url'] ?? null,
        ]);

        return back()->with('success', 'تم تحديث رابط الجلسة القادمة');
    }
}
