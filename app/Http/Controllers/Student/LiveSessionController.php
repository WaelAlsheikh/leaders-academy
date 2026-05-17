<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\Student;
use App\Services\Meetings\LiveSessionAttendanceService;
use App\Services\Meetings\LiveSessionManager;
use App\Services\Meetings\MeetingProviderManager;
use App\Services\Meetings\MeetingStandaloneWindowHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class LiveSessionController extends Controller
{
    public function __construct(
        private readonly LiveSessionManager $liveSessionManager,
        private readonly MeetingProviderManager $providerManager,
        private readonly LiveSessionAttendanceService $attendanceService,
    ) {}

    public function show(LiveSession $liveSession)
    {
        $student = $this->student();
        $this->authorizeStudent($liveSession, $student);

        $liveSession->load([
            'section.doctor',
            'section.registrableSubject',
            'section.semester.enrollmentCycle.registrableEntity',
            'section.semester.enrollmentCycle.archiveRecord',
        ]);

        $sessionState = $this->sessionState($liveSession, $student);
        $embedPayload = null;

        if (! $liveSession->ended_at) {
            $embedPayload = $this->providerManager
                ->for($liveSession->meeting_provider)
                ->buildEmbedPayload($liveSession, $student, 'student');

            $ttl = (int) config('meetings.meet_launch_url_ttl_minutes', 45);
            $embedPayload['meetLaunchUrl'] = URL::temporarySignedRoute(
                'student.live_sessions.meet',
                now()->addMinutes($ttl),
                ['liveSession' => $liveSession->id, 'actor' => $student->id]
            );
            unset($embedPayload['meetingUrl']);
        }

        $jitsiStandaloneWindow = MeetingStandaloneWindowHelper::shouldUse($embedPayload);

        $pageConfig = [
            'role' => 'student',
            'liveSessionId' => $liveSession->id,
            'embedEnabled' => ! $sessionState['ended'],
            'embedPayload' => $embedPayload,
            'jitsiStandaloneWindow' => $jitsiStandaloneWindow,
            'endpoints' => [
                'heartbeat' => route('student.live_sessions.heartbeat', $liveSession),
                'comments' => route('student.live_sessions.comments', $liveSession),
                'storeComment' => route('student.live_sessions.comments.store', $liveSession),
            ],
            'timers' => [
                'commentsPollSeconds' => (int) config('meetings.comment_poll_seconds', 5),
                'heartbeatIntervalSeconds' => (int) config('meetings.heartbeat_interval_seconds', 12),
            ],
            'initialState' => [
                'statusCode' => $sessionState['status_code'],
                'statusLabel' => $sessionState['status_label'],
                'canEmbed' => (bool) $sessionState['can_embed'],
                'doctorReady' => (bool) $sessionState['doctor_ready'],
                'commentsEnabled' => (bool) $sessionState['comments_enabled'],
                'commentsBlocked' => (bool) $sessionState['comments_blocked'],
                'ended' => (bool) $sessionState['ended'],
                'entryClosed' => (bool) $sessionState['entry_closed'],
            ],
            'branding' => [
                'logoUrl' => asset('favicon.svg'),
                'title' => 'Leaders Academy',
            ],
        ];

        return view('student.live_sessions.show', [
            'student' => $student,
            'liveSession' => $liveSession,
            'sessionState' => $sessionState,
            'embedPayload' => $embedPayload,
            'pageConfig' => $pageConfig,
            'jitsiStandaloneWindow' => $jitsiStandaloneWindow,
        ]);
    }

    public function heartbeat(Request $request, LiveSession $liveSession): JsonResponse
    {
        $student = $this->student();
        $this->authorizeStudent($liveSession, $student);

        $request->validate([
            'participant_id' => 'nullable|string|max:255',
        ]);

        $this->attendanceService->refreshPresence($liveSession);
        $attendance = $liveSession->attendances()->where('student_id', $student->id)->first();

        if ($liveSession->ended_at) {
            if ($attendance && $attendance->is_present) {
                $attendance->forceFill([
                    'is_present' => false,
                    'last_left_at' => now(config('app.timezone', 'UTC')),
                ])->save();
            }

            return response()->json([
                'ok' => true,
                'session' => $this->sessionState($liveSession, $student),
            ]);
        }

        if ($liveSession->entry_closed_at && ! $attendance) {
            return response()->json([
                'ok' => false,
                'message' => 'توقفت إمكانية الدخول لهذه الجلسة.',
                'session' => $this->sessionState($liveSession, $student),
            ], 403);
        }

        $attendance = $this->attendanceService->markStudentHeartbeat(
            $liveSession,
            $student,
            $request->string('participant_id')->toString() ?: null
        );

        return response()->json([
            'ok' => true,
            'session' => $this->sessionState($liveSession, $student),
            'attendance' => [
                'is_present' => $attendance->is_present,
                'join_count' => $attendance->join_count,
            ],
        ]);
    }

    public function comments(LiveSession $liveSession): JsonResponse
    {
        $student = $this->student();
        $this->authorizeStudent($liveSession, $student);

        $comments = $liveSession->comments()
            ->where('is_hidden', false)
            ->latest('id')
            ->limit(100)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn ($comment) => [
                'id' => $comment->id,
                'author_type' => $comment->author_type,
                'author_name' => $comment->author_name_snapshot,
                'body' => $comment->body,
                'created_at' => optional($comment->created_at)?->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'comments' => $comments,
            'session' => $this->sessionState($liveSession, $student),
        ]);
    }

    public function storeComment(Request $request, LiveSession $liveSession): JsonResponse
    {
        $student = $this->student();
        $this->authorizeStudent($liveSession, $student);

        $state = $this->sessionState($liveSession, $student);
        if ($liveSession->ended_at) {
            throw ValidationException::withMessages([
                'body' => 'انتهت هذه الجلسة.',
            ]);
        }

        if (! $state['comments_enabled']) {
            throw ValidationException::withMessages([
                'body' => 'التعليقات متوقفة حالياً.',
            ]);
        }

        if ($state['comments_blocked']) {
            throw ValidationException::withMessages([
                'body' => 'تم إيقاف إمكانية التعليق لحسابك في هذه الجلسة.',
            ]);
        }

        $data = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $liveSession->comments()->create([
            'author_type' => 'student',
            'author_id' => $student->id,
            'author_name_snapshot' => trim($student->first_name.' '.$student->last_name),
            'body' => $data['body'],
        ]);

        return response()->json([
            'ok' => true,
            'comment' => [
                'id' => $comment->id,
                'author_type' => $comment->author_type,
                'author_name' => $comment->author_name_snapshot,
                'body' => $comment->body,
                'created_at' => optional($comment->created_at)?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    private function student(): Student
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        abort_unless($student, 403);

        return $student;
    }

    private function authorizeStudent(LiveSession $liveSession, Student $student): void
    {
        $isEnrolled = $student->sections()
            ->wherePivot('status', 'active')
            ->where('class_sections.id', $liveSession->section_id)
            ->exists();

        abort_unless($isEnrolled, 403);
    }

    private function sessionState(LiveSession $liveSession, Student $student): array
    {
        $status = $this->liveSessionManager->scheduleStatusData(
            $liveSession,
            $liveSession->scheduled_starts_at?->copy()->startOfDay() ?? now(config('app.timezone', 'UTC'))->startOfDay(),
            $liveSession->scheduled_starts_at ?? now(config('app.timezone', 'UTC')),
            $liveSession->scheduled_ends_at ?? now(config('app.timezone', 'UTC')),
            now(config('app.timezone', 'UTC'))
        );
        $block = $liveSession->commentBlocks()
            ->where('student_id', $student->id)
            ->where('is_blocked', true)
            ->exists();

        return [
            'status_code' => $status['code'],
            'status_label' => $status['label'],
            'can_embed' => $liveSession->canStudentEnter(),
            'doctor_ready' => $liveSession->isDoctorReady(),
            'comments_enabled' => (bool) $liveSession->comments_enabled,
            'comments_blocked' => $block,
            'ended' => $liveSession->ended_at !== null,
            'entry_closed' => $liveSession->entry_closed_at !== null,
        ];
    }
}
