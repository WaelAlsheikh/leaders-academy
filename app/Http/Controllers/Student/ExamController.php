<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamGrade;
use App\Services\Exams\ExamAttemptService;
use App\Services\Exams\ExamCreationService;
use App\Services\Exams\ExamSchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamSchedulingService $schedulingService,
        private readonly ExamAttemptService $attemptService,
        private readonly ExamCreationService $creationService,
    ) {}

    public function index()
    {
        $student = $this->student();
        $this->schedulingService->syncStatuses();

        $sectionIds = $student->sections()
            ->where('student_sections.status', 'active')
            ->pluck('class_sections.id');

        $exams = Exam::query()
            ->whereIn('class_section_id', $sectionIds)
            ->whereIn('status', ['scheduled', 'running', 'finished'])
            ->with(['registrableSubject', 'classSection', 'doctor'])
            ->latest('starts_at')
            ->paginate(20);

        $grades = ExamGrade::query()
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->pluck('raw_score', 'exam_id');

        $attempts = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->pluck('status', 'exam_id');

        return view('student.exams.index', compact('student', 'exams', 'grades', 'attempts'));
    }

    public function show(Exam $exam)
    {
        $student = $this->student();
        $this->schedulingService->refreshExamStatus($exam);
        $exam->load(['registrableSubject', 'classSection', 'doctor', 'quizQuestions']);

        $attempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        $grade = ExamGrade::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        $canStart = $this->schedulingService->canStudentStart($exam) && ! $attempt;

        return view('student.exams.show', compact('student', 'exam', 'attempt', 'grade', 'canStart'));
    }

    public function start(Exam $exam)
    {
        try {
            $attempt = $this->attemptService->startAttempt($exam, $this->student());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('student.exams.attempt', $attempt);
    }

    public function attempt(ExamAttempt $attempt)
    {
        $student = $this->student();
        abort_unless($attempt->student_id === $student->id, 403);

        $attempt = $this->attemptService->expireIfNeeded($attempt);
        if ($attempt->isSubmitted()) {
            return redirect()->route('student.exams.result', $attempt);
        }

        $this->creationService->syncQuizChoicesFromBank($attempt->exam);
        $attempt->load(['exam.quizQuestions.choices', 'answers']);

        return view('student.exams.attempt', [
            'student' => $student,
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'autosaveInterval' => config('exams.autosave_interval_seconds', 15),
        ]);
    }

    public function autosave(Request $request, ExamAttempt $attempt): JsonResponse
    {
        abort_unless($attempt->student_id === $this->student()->id, 403);

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.exam_quiz_question_id' => 'required|integer',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.selected_choice_id' => 'nullable|integer',
            'answers.*.selected_choice_ids' => 'nullable|array',
        ]);

        try {
            $this->attemptService->autosave($attempt, $data['answers']);
        } catch (ValidationException $exception) {
            return response()->json(['ok' => false, 'errors' => $exception->errors()], 422);
        }

        return response()->json(['ok' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->student_id === $this->student()->id, 403);

        $data = $request->validate([
            'answers' => 'nullable|array',
            'answers.*.exam_quiz_question_id' => 'required|integer',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.selected_choice_id' => 'nullable|integer',
            'answers.*.selected_choice_ids' => 'nullable|array',
        ]);

        $this->attemptService->submit($attempt, $data['answers'] ?? []);

        return redirect()->route('student.exams.result', $attempt);
    }

    public function result(ExamAttempt $attempt)
    {
        abort_unless($attempt->student_id === $this->student()->id, 403);

        $attempt->load(['grade', 'exam']);

        return view('student.exams.result', [
            'student' => $this->student(),
            'attempt' => $attempt,
            'grade' => $attempt->grade,
            'preliminaryMessage' => config('exams.preliminary_result_message'),
        ]);
    }

    private function student()
    {
        return Auth::guard('student')->user();
    }
}
