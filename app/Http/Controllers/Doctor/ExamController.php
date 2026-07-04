<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamSetting;
use App\Services\Exams\ExamCreationService;
use App\Services\Exams\ExamSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamCreationService $creationService,
        private readonly ExamSchedulingService $schedulingService,
    ) {}

    public function index()
    {
        $doctor = $this->doctor();
        $this->schedulingService->syncStatuses();

        $exams = Exam::query()
            ->where('doctor_id', $doctor->id)
            ->with(['registrableSubject', 'classSection', 'quizQuestions'])
            ->latest('id')
            ->paginate(20);

        return view('doctor.exams.index', compact('doctor', 'exams'));
    }

    public function create()
    {
        abort_unless(ExamSetting::isManualMode(), 403);

        $doctor = $this->doctor();
        $sections = ClassSection::query()
            ->where('doctor_id', $doctor->id)
            ->with('registrableSubject')
            ->get();
        $questions = ExamQuestion::query()
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->whereNotNull('registrable_subject_id')
            ->with(['choices', 'registrableSubject'])
            ->orderBy('question_text')
            ->get();

        return view('doctor.exams.create', compact('doctor', 'sections', 'questions'));
    }

    public function store(Request $request)
    {
        abort_unless(ExamSetting::isManualMode(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_section_id' => 'required|exists:class_sections,id',
            'exam_date' => 'required|date',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'allow_late_entry' => 'nullable|boolean',
            'questions' => 'required|array|min:1',
            'questions.*.enabled' => 'nullable|boolean',
            'questions.*.question_id' => 'required|integer',
            'questions.*.points' => 'nullable|numeric|min:0.01',
        ]);

        $data['questions'] = collect($data['questions'])
            ->filter(fn ($row) => ! empty($row['enabled']))
            ->map(fn ($row) => [
                'question_id' => (int) $row['question_id'],
                'points' => (float) ($row['points'] ?? 1),
            ])
            ->values()
            ->all();

        if ($data['questions'] === []) {
            throw ValidationException::withMessages([
                'questions' => 'يجب اختيار سؤال واحد على الأقل.',
            ]);
        }

        try {
            $exam = $this->creationService->createManualExam($this->doctor(), $data);
            $this->creationService->schedule($exam);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('doctor.exams.index')->with('success', 'تم إنشاء وجدولة الامتحان.');
    }

    public function show(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->load(['quizQuestions.choices', 'grades.student', 'classSection', 'registrableSubject']);

        return view('doctor.exams.show', ['doctor' => $this->doctor(), 'exam' => $exam]);
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
