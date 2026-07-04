<?php

namespace App\Services\Exams;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamQuizQuestion;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamAttemptService
{
    public function __construct(
        private readonly ExamSchedulingService $scheduling,
        private readonly ExamGradingService $grading,
    ) {}

    public function startAttempt(Exam $exam, Student $student): ExamAttempt
    {
        $this->scheduling->refreshExamStatus($exam);

        if (! $this->scheduling->canStudentStart($exam)) {
            throw ValidationException::withMessages([
                'exam' => 'الامتحان غير متاح للبدء حالياً.',
            ]);
        }

        $isEnrolled = $exam->classSection
            ->students()
            ->where('students.id', $student->id)
            ->where('student_sections.status', 'active')
            ->exists();

        if (! $isEnrolled) {
            throw ValidationException::withMessages([
                'exam' => 'أنت غير مسجل في شعبة هذا الامتحان.',
            ]);
        }

        $existing = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            if ($existing->isSubmitted()) {
                throw ValidationException::withMessages([
                    'exam' => 'لقد قدمت هذا الامتحان مسبقاً.',
                ]);
            }

            return $existing->load(['answers', 'exam.quizQuestions.choices']);
        }

        return DB::transaction(function () use ($exam, $student) {
            $startedAt = Carbon::now(config('app.timezone', 'UTC'));
            $expiresAt = $startedAt->copy()->addMinutes($exam->duration_minutes);

            $examEnd = $exam->ends_at;
            if ($expiresAt->gt($examEnd)) {
                $expiresAt = $examEnd->copy();
            }

            $attempt = ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'status' => 'in_progress',
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
            ]);

            foreach ($exam->quizQuestions as $quizQuestion) {
                ExamAttemptAnswer::query()->create([
                    'attempt_id' => $attempt->id,
                    'exam_quiz_question_id' => $quizQuestion->id,
                    'question_id' => $quizQuestion->question_id,
                ]);
            }

            return $attempt->load(['answers', 'exam.quizQuestions.choices']);
        });
    }

    public function autosave(ExamAttempt $attempt, array $answers): ExamAttempt
    {
        $this->assertAttemptActive($attempt);

        DB::transaction(function () use ($attempt, $answers) {
            foreach ($answers as $answerData) {
                $this->saveAnswerRow($attempt, $answerData);
            }

            $attempt->forceFill([
                'last_autosave_at' => now(config('app.timezone', 'UTC')),
            ])->save();
        });

        return $attempt->fresh()->load('answers');
    }

    public function submit(ExamAttempt $attempt, array $answers = []): ExamAttempt
    {
        if ($attempt->isSubmitted()) {
            return $attempt;
        }

        if ($attempt->expires_at->isPast()) {
            $attempt->forceFill([
                'status' => 'expired',
                'submitted_at' => now(config('app.timezone', 'UTC')),
            ])->save();
        } else {
            DB::transaction(function () use ($attempt, $answers) {
                foreach ($answers as $answerData) {
                    $this->saveAnswerRow($attempt, $answerData);
                }

                $attempt->forceFill([
                    'status' => 'submitted',
                    'submitted_at' => now(config('app.timezone', 'UTC')),
                ])->save();
            });
        }

        $this->grading->gradeAttempt($attempt->fresh()->load(['answers.quizQuestion', 'exam.quizQuestions']));

        return $attempt->fresh()->load(['grade', 'answers']);
    }

    public function expireIfNeeded(ExamAttempt $attempt): ExamAttempt
    {
        if ($attempt->isSubmitted()) {
            return $attempt;
        }

        if ($attempt->expires_at->isPast()) {
            return $this->submit($attempt);
        }

        return $attempt;
    }

    private function assertAttemptActive(ExamAttempt $attempt): void
    {
        $attempt = $this->expireIfNeeded($attempt);

        if (! $attempt->isInProgress()) {
            throw ValidationException::withMessages([
                'attempt' => 'انتهت محاولة الامتحان.',
            ]);
        }
    }

    private function saveAnswerRow(ExamAttempt $attempt, array $data): void
    {
        $quizQuestionId = (int) ($data['exam_quiz_question_id'] ?? 0);
        $answer = $attempt->answers()->where('exam_quiz_question_id', $quizQuestionId)->first();

        if (! $answer) {
            return;
        }

        $quizQuestion = ExamQuizQuestion::query()->find($quizQuestionId);
        if (! $quizQuestion) {
            return;
        }

        if ($quizQuestion->type_snapshot === 'essay') {
            $answer->forceFill([
                'answer_text' => $data['answer_text'] ?? null,
                'selected_choice_id' => null,
                'selected_choice_ids' => null,
            ])->save();

            return;
        }

        if ($quizQuestion->type_snapshot === 'single_choice') {
            $answer->forceFill([
                'selected_choice_id' => $data['selected_choice_id'] ?? null,
                'selected_choice_ids' => null,
                'answer_text' => null,
            ])->save();

            return;
        }

        $answer->forceFill([
            'selected_choice_ids' => array_values(array_map('intval', $data['selected_choice_ids'] ?? [])),
            'selected_choice_id' => null,
            'answer_text' => null,
        ])->save();
    }
}
