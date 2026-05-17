<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\LiveSession;
use App\Models\Student;
use App\Services\Meetings\MeetingProviderManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveSessionMeetLaunchController extends Controller
{
    public function __construct(
        private readonly MeetingProviderManager $providerManager,
    ) {}

    public function student(Request $request, LiveSession $liveSession): RedirectResponse
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        abort_unless((int) $request->query('actor') === (int) $student->getKey(), 403);

        $this->authorizeStudentLiveSession($liveSession, $student);

        if ($liveSession->ended_at) {
            return redirect()->route('student.live_sessions.show', $liveSession)
                ->withErrors(['meet' => 'انتهت هذه الجلسة.']);
        }

        if (! $liveSession->canStudentEnter()) {
            return redirect()->route('student.live_sessions.show', $liveSession)
                ->withErrors([
                    'meet' => 'لا يمكن فتح قاعة الفيديو حالياً. تأكد أن المدرّس فعّل السماح بالدخول وأن الجلسة لم تُغلق.',
                ]);
        }

        $target = $this->jitsiMeetingUrl($liveSession, $student, 'student');

        return redirect()->away($target);
    }

    public function doctor(Request $request, LiveSession $liveSession): RedirectResponse
    {
        /** @var Doctor $doctor */
        $doctor = Auth::guard('doctor')->user();
        abort_unless($doctor, 403);
        abort_unless((int) $request->query('actor') === (int) $doctor->getKey(), 403);

        $this->authorizeDoctorLiveSession($liveSession, $doctor);

        if ($liveSession->ended_at) {
            return redirect()->route('doctor.live_sessions.show', $liveSession)
                ->withErrors(['meet' => 'انتهت هذه الجلسة.']);
        }

        $target = $this->jitsiMeetingUrl($liveSession, $doctor, 'doctor');

        return redirect()->away($target);
    }

    private function authorizeStudentLiveSession(LiveSession $liveSession, Student $student): void
    {
        $isEnrolled = $student->sections()
            ->wherePivot('status', 'active')
            ->where('class_sections.id', $liveSession->section_id)
            ->exists();

        abort_unless($isEnrolled, 403);
    }

    private function authorizeDoctorLiveSession(LiveSession $liveSession, Doctor $doctor): void
    {
        abort_unless($liveSession->section?->doctor_id === $doctor->id, 403);
    }

    private function jitsiMeetingUrl(LiveSession $liveSession, Doctor|Student $actor, string $role): string
    {
        $embed = $this->providerManager
            ->for($liveSession->meeting_provider)
            ->buildEmbedPayload($liveSession, $actor, $role);

        $url = $embed['meetingUrl'] ?? null;
        abort_if(! is_string($url) || $url === '', 503, 'تعذر تجهيز رابط قاعة الفيديو.');

        return $url;
    }
}
