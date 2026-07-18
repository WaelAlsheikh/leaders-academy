<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamGrade;
use App\Services\Exams\ExamGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExamGradingController extends Controller
{
    public function __construct(
        private readonly ExamGradingService $gradingService,
    ) {}

    public function index()
    {
        $doctor = $this->doctor();

        $grades = ExamGrade::query()
            ->whereHas('exam', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->with(['exam.registrableSubject', 'student'])
            ->latest('id')
            ->paginate(25);

        return view('doctor.exams.grades.index', compact('doctor', 'grades'));
    }

    public function review(Exam $exam)
    {
        $this->authorizeExam($exam);

        $attempts = $exam->attempts()
            ->with(['student', 'answers.quizQuestion.choices', 'answers.quizQuestion.question', 'grade'])
            ->whereIn('status', ['submitted', 'expired'])
            ->get();

        return view('doctor.exams.grades.review', [
            'doctor' => $this->doctor(),
            'exam' => $exam,
            'attempts' => $attempts,
        ]);
    }

    public function showAttempt(ExamAttempt $attempt)
    {
        abort_unless($attempt->isSubmitted(), 404);
        abort_unless($attempt->exam?->doctor_id === $this->doctor()->id, 403);

        $attempt->load([
            'student',
            'grade',
            'exam.registrableSubject',
            'answers.quizQuestion.choices',
            'answers.quizQuestion.question',
        ]);

        return view('doctor.exams.attempts.show', [
            'doctor' => $this->doctor(),
            'attempt' => $attempt,
            'canGradeEssays' => true,
        ]);
    }

    public function gradeEssay(Request $request, ExamAttemptAnswer $answer)
    {
        $doctor = $this->doctor();
        abort_unless($answer->quizQuestion?->exam?->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'points_awarded' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $this->gradingService->gradeEssayAnswer(
            $answer,
            $doctor,
            (float) $data['points_awarded'],
            $data['feedback'] ?? null
        );

        return back()->with('success', 'تم حفظ تصحيح السؤال التحريري.');
    }

    public function publish(ExamGrade $grade)
    {
        abort_unless($grade->exam?->doctor_id === $this->doctor()->id, 403);

        try {
            $this->gradingService->publishGrade($grade, null, $this->doctor());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'تم نشر النتيجة للطالب.');
    }

    private function doctor()
    {
        return Auth::guard('doctor')->user();
    }

    private function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->doctor_id === $this->doctor()->id, 403);
    }
}
