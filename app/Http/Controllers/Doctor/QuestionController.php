<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionCategory;
use App\Services\Exams\ExamQuestionBankQueryService;
use App\Services\Exams\ExamQuestionBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    public function __construct(
        private readonly ExamQuestionBankService $bankService,
        private readonly ExamQuestionBankQueryService $bankQuery,
    ) {}

    public function index(Request $request)
    {
        $doctor = $this->doctor();
        $categories = ExamQuestionCategory::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('name')
            ->get();

        $questions = ExamQuestion::query()
            ->where('doctor_id', $doctor->id)
            ->with(['category', 'choices', 'registrableSubject'])
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->registrable_subject_id, fn ($q) => $q->where('registrable_subject_id', $request->registrable_subject_id))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest('id')
            ->paginate(20);

        $subjects = $this->bankQuery->subjectsForDoctor($doctor);

        return view('doctor.exams.questions.index', compact('doctor', 'categories', 'questions', 'subjects'));
    }

    public function create(Request $request)
    {
        $doctor = $this->doctor();
        $subjects = $this->bankQuery->subjectsForDoctor($doctor);
        $categories = ExamQuestionCategory::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('name')
            ->get();

        return view('doctor.exams.questions.form', [
            'doctor' => $doctor,
            'subjects' => $subjects,
            'categories' => $categories,
            'choiceDefaults' => $this->choiceDefaults($request),
        ]);
    }

    public function store(Request $request)
    {
        $doctor = $this->doctor();
        $data = $this->validatedQuestionData($request);

        try {
            $this->bankService->createQuestion($doctor, $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('doctor.exams.questions.index')->with('success', 'تم إضافة السؤال.');
    }

    public function edit(Request $request, ExamQuestion $question)
    {
        $this->authorizeQuestion($question);
        $doctor = $this->doctor();
        $subjects = $this->bankQuery->subjectsForDoctor($doctor);
        $categories = ExamQuestionCategory::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('name')
            ->get();
        $question->load('choices');

        return view('doctor.exams.questions.form', [
            'doctor' => $doctor,
            'subjects' => $subjects,
            'categories' => $categories,
            'question' => $question,
            'choiceDefaults' => $this->choiceDefaults($request, $question),
        ]);
    }

    public function update(Request $request, ExamQuestion $question)
    {
        $this->authorizeQuestion($question);
        $data = $this->validatedQuestionData($request, $question);

        try {
            $this->bankService->updateQuestion($question, $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('doctor.exams.questions.index')->with('success', 'تم تحديث السؤال.');
    }

    public function destroy(ExamQuestion $question)
    {
        $this->authorizeQuestion($question);
        $question->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }

    private function validatedQuestionData(Request $request, ?ExamQuestion $question = null): array
    {
        $data = $request->validate([
            'registrable_subject_id' => 'required|exists:registrable_subjects,id',
            'category_id' => 'nullable|exists:exam_question_categories,id',
            'type' => 'required|in:single_choice,multiple_choice,essay',
            'question_text' => 'required|string',
            'default_points' => 'nullable|numeric|min:0.01',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'tags' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'required_with:choices|string',
            'choices.*.is_correct' => 'nullable|boolean',
        ]);

        if (! empty($data['tags'])) {
            $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
        } else {
            $data['tags'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $subjectIds = $this->bankQuery->subjectsForDoctor($this->doctor())->pluck('id')->all();
        if (! in_array((int) $data['registrable_subject_id'], $subjectIds, true)) {
            throw ValidationException::withMessages([
                'registrable_subject_id' => 'المادة المختارة غير مرتبطة بشعبك.',
            ]);
        }

        return $data;
    }

    private function doctor()
    {
        return Auth::guard('doctor')->user();
    }

    private function authorizeQuestion(ExamQuestion $question): void
    {
        abort_unless($question->doctor_id === $this->doctor()->id, 403);
    }

    /**
     * @return array<int, array{choice_text: string, is_correct: bool}>
     */
    private function choiceDefaults(Request $request, ?ExamQuestion $question = null): array
    {
        $old = old('choices');
        if (is_array($old)) {
            return array_values(array_map(fn ($choice) => [
                'choice_text' => $choice['choice_text'] ?? '',
                'is_correct' => ! empty($choice['is_correct']),
            ], $old));
        }

        if ($question && $question->relationLoaded('choices')) {
            return $question->choices->map(fn ($choice) => [
                'choice_text' => $choice->choice_text,
                'is_correct' => (bool) $choice->is_correct,
            ])->values()->all();
        }

        return [
            ['choice_text' => '', 'is_correct' => false],
            ['choice_text' => '', 'is_correct' => false],
        ];
    }
}
