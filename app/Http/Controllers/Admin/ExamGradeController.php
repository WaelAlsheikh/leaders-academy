<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\ExamGrade;
use App\Services\Exams\ExamGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamGradeController extends Controller
{
    public function __construct(
        private readonly ExamGradingService $gradingService,
    ) {}

    public function index(Request $request)
    {
        $grades = ExamGrade::query()
            ->with(['exam.registrableSubject', 'student', 'attempt'])
            ->latest('id')
            ->paginate(25);

        return view('admin.exams.grades.index', array_merge(
            compact('grades'),
            $this->portalViewData($request)
        ));
    }

    public function showAttempt(Request $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->isSubmitted(), 404);

        $attempt->load([
            'student',
            'grade',
            'exam.registrableSubject',
            'answers.quizQuestion.choices',
            'answers.quizQuestion.question',
        ]);

        return view('admin.exams.attempts.show', array_merge(
            compact('attempt'),
            $this->portalViewData($request)
        ));
    }

    public function approve(Request $request, ExamGrade $grade)
    {
        $this->gradingService->approveGrade($grade, Auth::guard('web')->user());

        return back()->with('success', 'تم اعتماد الدرجة.');
    }

    public function publish(Request $request, ExamGrade $grade)
    {
        $this->gradingService->publishGrade($grade, Auth::guard('web')->user());

        return back()->with('success', 'تم نشر الدرجة للطالب.');
    }

    private function portalViewData(Request $request): array
    {
        $portalContext = str_starts_with((string) $request->route()?->getName(), 'employee.')
            ? 'employee'
            : 'admin';

        return [
            'portalContext' => $portalContext,
            'routeBase' => $portalContext,
            'layout' => $portalContext === 'employee' ? 'layouts.app' : 'voyager::master',
            'hideNavbar' => $portalContext === 'employee',
            'bodyClass' => $portalContext === 'employee' ? 'employee-shell' : '',
        ];
    }
}
