<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestionCategory;
use App\Services\Exams\ExamQuestionBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuestionCategoryController extends Controller
{
    public function __construct(
        private readonly ExamQuestionBankService $bankService,
    ) {}

    public function index()
    {
        $doctor = $this->doctor();
        $categories = ExamQuestionCategory::query()
            ->where('doctor_id', $doctor->id)
            ->withCount('questions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('doctor.exams.categories.index', compact('categories', 'doctor'));
    }

    public function store(Request $request)
    {
        $doctor = $this->doctor();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:exam_question_categories,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->bankService->createCategory($doctor, $data);

        return back()->with('success', 'تم إنشاء التصنيف.');
    }

    public function update(Request $request, ExamQuestionCategory $category)
    {
        $this->authorizeCategory($category);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:exam_question_categories,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->bankService->updateCategory($category, $data);

        return back()->with('success', 'تم تحديث التصنيف.');
    }

    public function destroy(ExamQuestionCategory $category)
    {
        $this->authorizeCategory($category);
        $category->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }

    private function doctor()
    {
        return Auth::guard('doctor')->user();
    }

    private function authorizeCategory(ExamQuestionCategory $category): void
    {
        abort_unless($category->doctor_id === $this->doctor()->id, 403);
    }
}
