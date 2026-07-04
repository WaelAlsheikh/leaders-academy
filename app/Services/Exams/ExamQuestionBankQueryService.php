<?php

namespace App\Services\Exams;

use App\Models\ClassSection;
use App\Models\Doctor;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExamQuestionBankQueryService
{
    /**
     * @return Collection<int, \App\Models\RegistrableSubject>
     */
    public function subjectsForDoctor(Doctor $doctor): Collection
    {
        return ClassSection::query()
            ->where('doctor_id', $doctor->id)
            ->with('registrableSubject')
            ->get()
            ->pluck('registrableSubject')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * @param  array<int>|null  $categoryIds
     * @param  array<string>|null  $types
     */
    public function baseQuery(
        int $doctorId,
        int $registrableSubjectId,
        ?array $categoryIds = null,
        ?array $types = null,
    ): Builder {
        $query = ExamQuestion::query()
            ->where('doctor_id', $doctorId)
            ->where('registrable_subject_id', $registrableSubjectId)
            ->where('is_active', true);

        if (! empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $allowedTypes = $types ?: array_keys(config('exams.question_types', []));
        $query->whereIn('type', $allowedTypes);

        $query->where(function (Builder $q) {
            $q->where('type', 'essay')
                ->orWhere(function (Builder $choiceQuery) {
                    $choiceQuery->whereIn('type', ['single_choice', 'multiple_choice'])
                        ->has('choices', '>=', 2)
                        ->whereHas('choices', fn (Builder $c) => $c->where('is_correct', true));
                });
        });

        return $query;
    }

    /**
     * @param  array<int>|null  $categoryIds
     * @param  array<string>|null  $types
     * @return array{total: int, by_type: array<string, int>}
     */
    public function bankStats(
        int $doctorId,
        int $registrableSubjectId,
        ?array $categoryIds = null,
        ?array $types = null,
    ): array {
        $questions = $this->baseQuery($doctorId, $registrableSubjectId, $categoryIds, $types)->get(['type']);

        $byType = [];
        foreach (array_keys(config('exams.question_types', [])) as $type) {
            $byType[$type] = $questions->where('type', $type)->count();
        }

        return [
            'total' => $questions->count(),
            'by_type' => $byType,
        ];
    }

    public function sectionContext(ClassSection $section): array
    {
        $section->load(['registrableSubject', 'doctor', 'semester']);
        $section->loadCount(['students' => fn ($q) => $q->where('student_sections.status', 'active')]);

        $doctorId = (int) $section->doctor_id;
        $subjectId = (int) $section->registrable_subject_id;

        $categories = ExamQuestionCategory::query()
            ->where('doctor_id', $doctorId)
            ->where(function ($q) use ($subjectId) {
                $q->where('registrable_subject_id', $subjectId)
                    ->orWhereNull('registrable_subject_id');
            })
            ->withCount(['questions' => fn ($q) => $q
                ->where('registrable_subject_id', $subjectId)
                ->where('is_active', true)
                ->where(function (Builder $query) {
                    $query->where('type', 'essay')
                        ->orWhere(function (Builder $choiceQuery) {
                            $choiceQuery->whereIn('type', ['single_choice', 'multiple_choice'])
                                ->has('choices', '>=', 2);
                        });
                }),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'questions_count' => $c->questions_count,
            ]);

        return [
            'section_id' => $section->id,
            'section_name' => $section->name,
            'subject_id' => $subjectId,
            'subject_name' => $section->registrableSubject?->name,
            'doctor_id' => $doctorId,
            'doctor_name' => $section->doctor?->full_name,
            'semester_name' => $section->semester?->name,
            'students_count' => (int) $section->students_count,
            'categories' => $categories,
            'bank_stats' => $this->bankStats($doctorId, $subjectId),
        ];
    }

    public function queryForExam(Exam $exam): Builder
    {
        return $this->baseQuery(
            (int) $exam->doctor_id,
            (int) $exam->registrable_subject_id,
            $exam->category_ids ?: null,
            $exam->question_types_filter ?: null,
        );
    }
}
