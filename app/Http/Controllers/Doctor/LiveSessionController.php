<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\LiveSession;
use App\Models\LiveSessionComment;
use App\Models\LiveSessionCommentBlock;
use App\Models\SectionMeeting;
use App\Services\Meetings\LiveSessionAttendanceService;
use App\Services\Meetings\LiveSessionManager;
use App\Services\Meetings\MeetingProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Illuminate\Validation\ValidationException;

class LiveSessionController extends Controller
{
    public function __construct(
        private readonly LiveSessionManager $liveSessionManager,
        private readonly MeetingProviderManager $providerManager,
        private readonly LiveSessionAttendanceService $attendanceService,
    ) {
    }

    public function start(Request $request, SectionMeeting $meeting): RedirectResponse
    {
        $doctor = $this->doctor();
        $meeting->load('section.semester.enrollmentCycle.archiveRecord');
        $this->authorizeMeeting($meeting, $doctor);

        if ($meeting->section?->semester?->enrollmentCycle?->is_archived) {
            return back()->withErrors([
                'meeting' => 'لا يمكن بدء جلسة ضمن دورة مؤرشفة.',
            ]);
        }

        $data = $request->validate([
            'occurrence_date' => 'required|date',
        ]);

        $occurrenceDate = Carbon::parse($data['occurrence_date'], config('app.timezone', 'UTC'))->toDateString();
        $today = Carbon::now(config('app.timezone', 'UTC'))->toDateString();

        if ($occurrenceDate !== $today) {
            return back()->withErrors([
                'meeting' => 'يمكن بدء الجلسة فقط في يوم المحاضرة نفسه.',
            ]);
        }

        try {
            $liveSession = $this->liveSessionManager->startForOccurrence($meeting, $occurrenceDate, $doctor);
        } catch (ValidationException $exception) {
            return back()->withErrors([
                'meeting' => collect($exception->errors())->flatten()->first() ?: 'تعذر بدء الجلسة.',
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'meeting' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('doctor.live_sessions.show', $liveSession)
            ->with('success', 'تم بدء الجلسة بنجاح.');
    }

    public function show(LiveSession $liveSession)
    {
        $doctor = $this->doctor();
        $liveSession->load([
            'section.doctor',
            'section.students',
            'section.registrableSubject',
            'section.semester.enrollmentCycle.registrableEntity',
            'section.semester.enrollmentCycle.archiveRecord',
            'sectionMeeting',
        ]);
        $this->authorizeLiveSession($liveSession, $doctor);

        $embedPayload = null;
        if (! $liveSession->ended_at) {
            $embedPayload = $this->providerManager
                ->for($liveSession->meeting_provider)
                ->buildEmbedPayload($liveSession, $doctor, 'doctor');
        }

        $status = $this->liveSessionManager->statusData($liveSession);

        $pageConfig = [
            'role' => 'doctor',
            'liveSessionId' => $liveSession->id,
            'embedEnabled' => ! $liveSession->ended_at,
            'embedPayload' => $embedPayload,
            'endpoints' => [
                'attendance' => route('doctor.live_sessions.attendance', $liveSession),
                'comments' => route('doctor.live_sessions.comments', $liveSession),
                'storeComment' => route('doctor.live_sessions.comments.store', $liveSession),
                'hideCommentBase' => url('/doctor/live-sessions/' . $liveSession->id . '/comments'),
                'commentBlocks' => route('doctor.live_sessions.comment_blocks', $liveSession),
                'hostPresence' => route('doctor.live_sessions.host_presence', $liveSession),
                'moderation' => route('doctor.live_sessions.moderation', $liveSession),
            ],
            'timers' => [
                'commentsPollSeconds' => (int) config('meetings.comment_poll_seconds', 5),
                'attendancePollSeconds' => 10,
            ],
            'initialState' => [
                'statusCode' => $status['code'],
                'statusLabel' => $status['label'],
                'commentsEnabled' => (bool) $liveSession->comments_enabled,
                'audioModerationEnabled' => (bool) $liveSession->audio_moderation_enabled,
                'videoModerationEnabled' => (bool) $liveSession->video_moderation_enabled,
            ],
            'branding' => [
                'logoUrl' => asset('assets/images/logo.png'),
                'title' => 'أكاديمية ليدرز',
            ],
        ];

        return view('doctor.live_sessions.show', [
            'doctor' => $doctor,
            'liveSession' => $liveSession,
            'status' => $status,
            'embedPayload' => $embedPayload,
            'pageConfig' => $pageConfig,
        ]);
    }

    public function closeEntry(LiveSession $liveSession): RedirectResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $this->liveSessionManager->closeEntry($liveSession, $doctor);

        return back()->with('success', 'تم إيقاف إمكانية الدخول لهذه الجلسة.');
    }

    public function reopenEntry(LiveSession $liveSession): RedirectResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $this->liveSessionManager->reopenEntry($liveSession);

        return back()->with('success', 'تمت إعادة فتح الدخول للجلسة.');
    }

    public function end(LiveSession $liveSession): RedirectResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $this->setHostPresence($liveSession, $doctor, false);
        $this->liveSessionManager->end($liveSession, $doctor);

        return back()->with('success', 'تم إنهاء الجلسة.');
    }

    public function updateHostPresence(Request $request, LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $data = $request->validate([
            'is_ready' => 'required|boolean',
        ]);

        $isReady = ! $liveSession->ended_at && (bool) $data['is_ready'];
        $liveSession = $this->setHostPresence($liveSession, $doctor, $isReady);

        return response()->json([
            'ok' => true,
            'host_ready' => $liveSession->isDoctorReady(),
            'session' => $this->liveSessionManager->statusData($liveSession),
        ]);
    }

    public function attendance(LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $liveSession->load([
            'section.students' => fn ($query) => $query->orderBy('first_name')->orderBy('last_name'),
            'attendances',
            'commentBlocks',
        ]);
        $this->authorizeLiveSession($liveSession, $doctor);

        $this->attendanceService->refreshPresence($liveSession);
        $liveSession->load('attendances');

        $attendanceMap = $liveSession->attendances->keyBy('student_id');
        $blockMap = $liveSession->commentBlocks->keyBy('student_id');

        $students = $liveSession->section->students->map(function ($student) use ($attendanceMap, $blockMap) {
            $attendance = $attendanceMap->get($student->id);
            $block = $blockMap->get($student->id);

            return [
                'id' => $student->id,
                'full_name' => trim($student->first_name . ' ' . $student->last_name),
                'username' => $student->username,
                'joined' => $attendance?->first_joined_at?->format('Y-m-d H:i:s'),
                'last_seen' => $attendance?->last_seen_at?->format('Y-m-d H:i:s'),
                'join_count' => $attendance?->join_count ?? 0,
                'is_present' => (bool) ($attendance?->is_present),
                'has_joined' => $attendance !== null,
                'comments_blocked' => (bool) ($block?->is_blocked),
                'jitsi_participant_id' => $attendance?->jitsi_participant_id,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_students' => $students->count(),
                'joined_students' => $students->where('has_joined', true)->count(),
                'present_students' => $students->where('is_present', true)->count(),
            ],
            'students' => $students,
            'session' => $this->liveSessionManager->statusData($liveSession),
        ]);
    }

    public function comments(LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $comments = $liveSession->comments()
            ->latest('id')
            ->limit(100)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn (LiveSessionComment $comment) => [
                'id' => $comment->id,
                'author_type' => $comment->author_type,
                'author_name' => $comment->author_name_snapshot,
                'body' => $comment->body,
                'created_at' => optional($comment->created_at)?->format('Y-m-d H:i:s'),
                'is_hidden' => (bool) $comment->is_hidden,
            ]);

        return response()->json([
            'comments' => $comments,
            'session' => [
                ...$this->liveSessionManager->statusData($liveSession),
                'comments_enabled' => (bool) $liveSession->comments_enabled,
                'audio_moderation_enabled' => (bool) $liveSession->audio_moderation_enabled,
                'video_moderation_enabled' => (bool) $liveSession->video_moderation_enabled,
            ],
        ]);
    }

    public function storeComment(Request $request, LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        if ($liveSession->ended_at) {
            throw ValidationException::withMessages([
                'body' => 'انتهت هذه الجلسة.',
            ]);
        }

        $data = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $liveSession->comments()->create([
            'author_type' => 'doctor',
            'author_id' => $doctor->id,
            'author_name_snapshot' => $doctor->full_name,
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
                'is_hidden' => false,
            ],
        ]);
    }

    public function hideComment(LiveSession $liveSession, LiveSessionComment $comment): JsonResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);
        abort_unless($comment->live_session_id === $liveSession->id, 404);

        $comment->forceFill([
            'is_hidden' => true,
            'hidden_at' => now(config('app.timezone', 'UTC')),
            'hidden_by_doctor_id' => $doctor->id,
        ])->save();

        return response()->json(['ok' => true]);
    }

    public function updateCommentBlocks(Request $request, LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $liveSession->load('section.students');
        $this->authorizeLiveSession($liveSession, $doctor);

        $data = $request->validate([
            'action' => 'required|in:block,unblock',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $allowedStudentIds = $liveSession->section->students->pluck('id')->all();
        foreach ($data['student_ids'] as $studentId) {
            abort_unless(in_array((int) $studentId, $allowedStudentIds, true), 403);
        }

        foreach ($data['student_ids'] as $studentId) {
            LiveSessionCommentBlock::query()->updateOrCreate(
                [
                    'live_session_id' => $liveSession->id,
                    'student_id' => $studentId,
                ],
                [
                    'is_blocked' => $data['action'] === 'block',
                    'blocked_at' => $data['action'] === 'block'
                        ? now(config('app.timezone', 'UTC'))
                        : null,
                    'blocked_by_doctor_id' => $data['action'] === 'block' ? $doctor->id : null,
                ]
            );
        }

        return response()->json([
            'ok' => true,
            'action' => $data['action'],
        ]);
    }

    public function updateModeration(Request $request, LiveSession $liveSession): JsonResponse
    {
        $doctor = $this->doctor();
        $this->authorizeLiveSession($liveSession, $doctor);

        $data = $request->validate([
            'comments_enabled' => 'nullable|boolean',
            'audio_moderation_enabled' => 'nullable|boolean',
            'video_moderation_enabled' => 'nullable|boolean',
        ]);

        $liveSession->forceFill([
            'comments_enabled' => array_key_exists('comments_enabled', $data)
                ? (bool) $data['comments_enabled']
                : $liveSession->comments_enabled,
            'audio_moderation_enabled' => array_key_exists('audio_moderation_enabled', $data)
                ? (bool) $data['audio_moderation_enabled']
                : $liveSession->audio_moderation_enabled,
            'video_moderation_enabled' => array_key_exists('video_moderation_enabled', $data)
                ? (bool) $data['video_moderation_enabled']
                : $liveSession->video_moderation_enabled,
        ])->save();

        return response()->json([
            'ok' => true,
            'session' => [
                'comments_enabled' => (bool) $liveSession->comments_enabled,
                'audio_moderation_enabled' => (bool) $liveSession->audio_moderation_enabled,
                'video_moderation_enabled' => (bool) $liveSession->video_moderation_enabled,
            ],
        ]);
    }

    private function doctor(): Doctor
    {
        /** @var Doctor $doctor */
        $doctor = Auth::guard('doctor')->user();
        abort_unless($doctor, 403);

        return $doctor;
    }

    private function authorizeMeeting(SectionMeeting $meeting, Doctor $doctor): void
    {
        $section = $meeting->section;

        abort_unless($section, 404);
        abort_unless($section->doctor_id === $doctor->id, 403);
        abort_unless($section->mode === 'online', 403);
    }

    private function authorizeLiveSession(LiveSession $liveSession, Doctor $doctor): void
    {
        abort_unless($liveSession->section?->doctor_id === $doctor->id, 403);
    }

    private function setHostPresence(LiveSession $liveSession, Doctor $doctor, bool $isReady): LiveSession
    {
        $payload = $liveSession->provider_payload ?? [];
        $payload['host_presence'] = [
            'is_ready' => $isReady,
            'doctor_id' => $doctor->id,
            'updated_at' => now(config('app.timezone', 'UTC'))->toIso8601String(),
        ];

        $liveSession->forceFill([
            'provider_payload' => $payload,
        ])->save();

        return $liveSession->fresh();
    }
}
