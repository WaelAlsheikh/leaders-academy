<?php

namespace App\Services\Exams;

use App\Models\Doctor;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionCategory;
use App\Models\ExamQuestionChoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamQuestionBankService
{
    public function createCategory(Doctor $doctor, array $data): ExamQuestionCategory
    {
        return ExamQuestionCategory::query()->create([
            'doctor_id' => $doctor->id,
            'registrable_subject_id' => $data['registrable_subject_id'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateCategory(ExamQuestionCategory $category, array $data): ExamQuestionCategory
    {
        $category->update([
            'registrable_subject_id' => $data['registrable_subject_id'] ?? $category->registrable_subject_id,
            'parent_id' => $data['parent_id'] ?? $category->parent_id,
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return $category->fresh();
    }

    public function createQuestion(Doctor $doctor, array $data): ExamQuestion
    {
        return DB::transaction(function () use ($doctor, $data) {
            $question = ExamQuestion::query()->create([
                'doctor_id' => $doctor->id,
                'registrable_subject_id' => $data['registrable_subject_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'default_points' => $data['default_points'] ?? 1,
                'difficulty' => $data['difficulty'] ?? null,
                'tags' => $data['tags'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (in_array($question->type, ['single_choice', 'multiple_choice'], true)) {
                $this->syncChoices($question, $data['choices'] ?? []);
            }

            return $question->load('choices');
        });
    }

    public function updateQuestion(ExamQuestion $question, array $data): ExamQuestion
    {
        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'registrable_subject_id' => $data['registrable_subject_id'] ?? $question->registrable_subject_id,
                'category_id' => $data['category_id'] ?? $question->category_id,
                'type' => $data['type'] ?? $question->type,
                'question_text' => $data['question_text'] ?? $question->question_text,
                'default_points' => $data['default_points'] ?? $question->default_points,
                'difficulty' => $data['difficulty'] ?? $question->difficulty,
                'tags' => $data['tags'] ?? $question->tags,
                'is_active' => $data['is_active'] ?? $question->is_active,
            ]);

            if (in_array($question->type, ['single_choice', 'multiple_choice'], true)) {
                $this->syncChoices($question, $data['choices'] ?? []);
            } else {
                $question->choices()->delete();
            }

            return $question->fresh()->load('choices');
        });
    }

    /**
     * @param  array<int, array{choice_text: string, is_correct?: bool}>  $choices
     */
    private function syncChoices(ExamQuestion $question, array $choices): void
    {
        if (count($choices) < 2) {
            throw ValidationException::withMessages([
                'choices' => 'يجب إضافة خيارين على الأقل.',
            ]);
        }

        $correctCount = collect($choices)->filter(fn ($choice) => ! empty($choice['is_correct']))->count();
        if ($correctCount < 1) {
            throw ValidationException::withMessages([
                'choices' => 'يجب تحديد إجابة صحيحة واحدة على الأقل.',
            ]);
        }

        if ($question->type === 'single_choice' && $correctCount !== 1) {
            throw ValidationException::withMessages([
                'choices' => 'سؤال الاختيار الواحد يجب أن يحتوي على إجابة صحيحة واحدة فقط.',
            ]);
        }

        $question->choices()->delete();

        foreach (array_values($choices) as $index => $choice) {
            ExamQuestionChoice::query()->create([
                'question_id' => $question->id,
                'choice_text' => $choice['choice_text'],
                'is_correct' => (bool) ($choice['is_correct'] ?? false),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
