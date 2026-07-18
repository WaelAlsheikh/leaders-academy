<?php

namespace App\Services\Exams;

use App\Models\ClassSection;
use App\Models\Doctor;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuizQuestion;
use App\Models\ExamQuizQuestionChoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamCreationService
{
    public function __construct(
        private readonly ExamPointsDistributionService $pointsDistribution,
        private readonly ExamQuestionBankQueryService $bankQuery,
    ) {}

    public function createRandomDraft(array $data, ?User $creator = null): Exam
    {
        return DB::transaction(function () use ($data, $creator) {
            $section = ClassSection::query()
                ->with(['semester', 'registrableSubject', 'doctor'])
                ->findOrFail($data['class_section_id']);

            if (! $section->registrable_subject_id) {
                throw ValidationException::withMessages([
                    'class_section_id' => 'الشعبة المختارة غير مرتبطة بمادة مسجّلة.',
                ]);
            }

            if (! $section->doctor_id) {
                throw ValidationException::withMessages([
                    'class_section_id' => 'الشعبة المختارة غير مرتبطة بدكتور.',
                ]);
            }

            $doctorId = (int) $section->doctor_id;
            $categoryIds = ! empty($data['category_ids']) ? array_map('intval', $data['category_ids']) : null;
            $typeFilter = ! empty($data['question_types']) ? array_values($data['question_types']) : null;
            $questionCount = (int) $data['question_count'];

            $available = $this->bankQuery->bankStats(
                $doctorId,
                (int) $section->registrable_subject_id,
                $categoryIds,
                $typeFilter,
            );

            if ($available['total'] < $questionCount) {
                throw ValidationException::withMessages([
                    'question_count' => "بنك أسئلة الدكتور للمادة «{$section->registrableSubject?->name}» يحتوي على {$available['total']} سؤالاً نشطاً فقط، بينما طلبت {$questionCount}.",
                ]);
            }

            $exam = Exam::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'registrable_subject_id' => $section->registrable_subject_id,
                'class_section_id' => $section->id,
                'doctor_id' => $doctorId,
                'semester_id' => $section->semester_id,
                'creation_mode' => 'random',
                'question_count' => $questionCount,
                'category_ids' => $categoryIds,
                'question_types_filter' => $typeFilter,
                'total_points' => config('exams.default_total_points', 100),
                'status' => 'draft',
                'exam_date' => $data['exam_date'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'duration_minutes' => (int) $data['duration_minutes'],
                'allow_late_entry' => (bool) ($data['allow_late_entry'] ?? false),
                'questions_locked' => false,
                'created_by' => $creator?->id,
            ]);

            $this->regenerateRandomQuestions($exam);

            return $exam->fresh()->load('quizQuestions.choices');
        });
    }

    public function regenerateRandomQuestions(Exam $exam): Exam
    {
        if ($exam->questions_locked) {
            throw ValidationException::withMessages([
                'exam' => 'تم اعتماد أسئلة هذا الامتحان ولا يمكن إعادة التوليد.',
            ]);
        }

        if ($exam->creation_mode !== 'random') {
            throw ValidationException::withMessages([
                'exam' => 'هذا الامتحان ليس من نوع التوليد العشوائي.',
            ]);
        }

        $questions = $this->bankQuery
            ->queryForExam($exam)
            ->with('choices')
            ->inRandomOrder()
            ->limit($exam->question_count)
            ->get();

        if ($questions->count() < $exam->question_count) {
            throw ValidationException::withMessages([
                'question_count' => 'بنك أسئلة الدكتور للمادة المحددة لا يحتوي على عدد كافٍ من الأسئلة الصالحة (مع خيارات) ضمن الفلاتر المختارة.',
            ]);
        }

        return $this->attachQuestionsToExam($exam, $questions, (float) $exam->total_points);
    }

    public function createManualExam(Doctor $doctor, array $data): Exam
    {
        return DB::transaction(function () use ($doctor, $data) {
            $section = ClassSection::query()->findOrFail($data['class_section_id']);

            abort_unless($section->doctor_id === $doctor->id, 403);

            $questionIds = collect($data['questions'])->pluck('question_id')->all();
            $questions = ExamQuestion::query()
                ->where('doctor_id', $doctor->id)
                ->where('registrable_subject_id', $section->registrable_subject_id)
                ->whereIn('id', $questionIds)
                ->with('choices')
                ->get()
                ->keyBy('id');

            if ($questions->count() !== count($questionIds)) {
                throw ValidationException::withMessages([
                    'questions' => 'بعض الأسئلة المحددة غير متاحة في بنك أسئلتك.',
                ]);
            }

            $totalPoints = collect($data['questions'])->sum(fn ($row) => (float) $row['points']);

            $exam = Exam::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'registrable_subject_id' => $section->registrable_subject_id,
                'class_section_id' => $section->id,
                'doctor_id' => $doctor->id,
                'semester_id' => $section->semester_id,
                'creation_mode' => 'manual',
                'question_count' => count($questionIds),
                'total_points' => $totalPoints,
                'status' => 'draft',
                'exam_date' => $data['exam_date'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'duration_minutes' => (int) $data['duration_minutes'],
                'allow_late_entry' => (bool) ($data['allow_late_entry'] ?? false),
                'questions_locked' => true,
                'approved_at' => now(),
            ]);

            $ordered = collect($data['questions'])->map(function ($row) use ($questions) {
                $question = $questions[$row['question_id']];
                $question->manual_points = (float) $row['points'];

                return $question;
            });

            $this->attachQuestionsToExam($exam, $ordered, null, true);

            return $exam->fresh()->load('quizQuestions.choices');
        });
    }

    public function approveRandomQuestions(Exam $exam): Exam
    {
        if ($exam->creation_mode !== 'random') {
            throw ValidationException::withMessages([
                'exam' => 'يمكن اعتماد الأسئلة للامتحانات العشوائية فقط من هذه الواجهة.',
            ]);
        }

        if ($exam->quizQuestions()->count() < 1) {
            throw ValidationException::withMessages([
                'exam' => 'لا توجد أسئلة لاعتمادها.',
            ]);
        }

        $exam->forceFill([
            'questions_locked' => true,
            'approved_at' => now(),
        ])->save();

        return $exam->fresh();
    }

    public function schedule(Exam $exam): Exam
    {
        if (! $exam->questions_locked || $exam->quizQuestions()->count() < 1) {
            throw ValidationException::withMessages([
                'exam' => 'يجب اعتماد أسئلة الامتحان قبل الجدولة.',
            ]);
        }

        $exam->forceFill(['status' => 'scheduled'])->save();

        return $exam->fresh();
    }

    public function syncQuizChoicesFromBank(Exam $exam): int
    {
        $exam->load(['quizQuestions.choices', 'quizQuestions.question.choices']);
        $synced = 0;

        foreach ($exam->quizQuestions as $quizQuestion) {
            if ($quizQuestion->choices->isNotEmpty()) {
                continue;
            }

            if ($quizQuestion->type_snapshot === 'essay') {
                continue;
            }

            $question = $quizQuestion->question ?: ExamQuestion::with('choices')->find($quizQuestion->question_id);
            if (! $question || $question->choices->count() < 2) {
                continue;
            }

            foreach ($question->choices as $choice) {
                ExamQuizQuestionChoice::query()->create([
                    'exam_quiz_question_id' => $quizQuestion->id,
                    'choice_id' => $choice->id,
                    'choice_text' => $choice->choice_text,
                    'is_correct' => $choice->is_correct,
                    'sort_order' => $choice->sort_order,
                ]);
            }

            $synced++;
        }

        return $synced;
    }

    /**
     * @param  Collection<int, ExamQuestion>  $questions
     */
    private function attachQuestionsToExam(
        Exam $exam,
        Collection $questions,
        ?float $totalPoints = null,
        bool $useManualPoints = false,
    ): Exam {
        $exam->quizQuestions()->each(function (ExamQuizQuestion $quizQuestion) {
            $quizQuestion->choices()->delete();
            $quizQuestion->delete();
        });

        $pointsList = $useManualPoints
            ? $questions->map(fn (ExamQuestion $q) => (float) ($q->manual_points ?? $q->default_points))->all()
            : $this->pointsDistribution->distribute($questions->count(), $totalPoints ?? 100);

        foreach ($questions->values() as $index => $question) {
            $quizQuestion = ExamQuizQuestion::query()->create([
                'exam_id' => $exam->id,
                'question_id' => $question->id,
                'sort_order' => $index + 1,
                'points' => $pointsList[$index] ?? 0,
                'question_text_snapshot' => $question->question_text,
                'image_path_snapshot' => $question->image_path,
                'type_snapshot' => $question->type,
            ]);

            foreach ($question->choices as $choice) {
                ExamQuizQuestionChoice::query()->create([
                    'exam_quiz_question_id' => $quizQuestion->id,
                    'choice_id' => $choice->id,
                    'choice_text' => $choice->choice_text,
                    'is_correct' => $choice->is_correct,
                    'sort_order' => $choice->sort_order,
                ]);
            }
        }

        if (! $useManualPoints) {
            $exam->forceFill([
                'total_points' => array_sum($pointsList),
                'question_count' => $questions->count(),
            ])->save();
        }

        return $exam->fresh();
    }
}
