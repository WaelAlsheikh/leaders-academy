<?php

namespace App\Services\Exams;

use App\Models\Doctor;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamGrade;
use App\Models\ExamGradeReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamGradingService
{
    public function gradeAttempt(ExamAttempt $attempt): ExamGrade
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->load(['answers.quizQuestion.choices', 'exam']);

            $maxScore = (float) $attempt->exam->total_points;
            $rawScore = 0;
            $needsReview = false;

            foreach ($attempt->answers as $answer) {
                $quizQuestion = $answer->quizQuestion;
                if (! $quizQuestion) {
                    continue;
                }

                if ($quizQuestion->type_snapshot === 'essay') {
                    $needsReview = true;
                    $answer->forceFill([
                        'is_correct' => null,
                        'points_awarded' => null,
                    ])->save();

                    continue;
                }

                $awarded = $this->scoreAutoGradableAnswer($answer, $quizQuestion);
                $rawScore += $awarded;

                $answer->forceFill([
                    'is_correct' => $awarded > 0,
                    'points_awarded' => $awarded,
                ])->save();
            }

            $percentage = $maxScore > 0 ? round(($rawScore / $maxScore) * 100, 2) : 0;

            $status = $needsReview ? 'pending_review' : 'auto_corrected';

            $grade = ExamGrade::query()->updateOrCreate(
                [
                    'exam_id' => $attempt->exam_id,
                    'student_id' => $attempt->student_id,
                ],
                [
                    'attempt_id' => $attempt->id,
                    'raw_score' => $rawScore,
                    'max_score' => $maxScore,
                    'percentage' => $percentage,
                    'status' => $status,
                ]
            );

            return $grade;
        });
    }

    public function gradeEssayAnswer(ExamAttemptAnswer $answer, Doctor $doctor, float $points, ?string $feedback = null): ExamAttemptAnswer
    {
        abort_unless($answer->quizQuestion?->type_snapshot === 'essay', 422);

        $maxPoints = (float) $answer->quizQuestion->points;
        $points = max(0, min($points, $maxPoints));

        $answer->forceFill([
            'points_awarded' => $points,
            'is_correct' => $points >= $maxPoints,
            'graded_by_doctor_id' => $doctor->id,
            'graded_at' => now(config('app.timezone', 'UTC')),
            'feedback' => $feedback,
        ])->save();

        $answer->load('attempt');
        $this->recalculateGrade($answer->attempt, $doctor);

        return $answer->fresh();
    }

    public function recalculateGrade(ExamAttempt $attempt, ?Doctor $doctor = null): ExamGrade
    {
        $attempt->load(['answers.quizQuestion', 'exam', 'grade']);

        $rawScore = (float) $attempt->answers->sum(fn ($a) => (float) ($a->points_awarded ?? 0));
        $maxScore = (float) $attempt->exam->total_points;
        $percentage = $maxScore > 0 ? round(($rawScore / $maxScore) * 100, 2) : 0;

        $hasUngradedEssay = $attempt->answers
            ->filter(fn ($a) => $a->quizQuestion?->type_snapshot === 'essay')
            ->contains(fn ($a) => $a->points_awarded === null);

        $grade = $attempt->grade ?? ExamGrade::query()->create([
            'exam_id' => $attempt->exam_id,
            'attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'status' => 'draft',
        ]);

        $status = $grade->status;
        if ($hasUngradedEssay) {
            $status = 'pending_review';
        } elseif (in_array($status, ['pending_review', 'draft'], true)) {
            $status = 'reviewed';
        }

        $grade->forceFill([
            'raw_score' => $rawScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'status' => $status,
            'reviewed_by_doctor_id' => $doctor?->id ?? $grade->reviewed_by_doctor_id,
        ])->save();

        return $grade->fresh();
    }

    public function approveGrade(ExamGrade $grade, ?User $user = null): ExamGrade
    {
        $grade->forceFill([
            'status' => 'approved',
            'approved_by' => $user?->id,
        ])->save();

        ExamGradeReview::query()->create([
            'grade_id' => $grade->id,
            'reviewer_user_id' => $user?->id,
            'action' => 'approved',
        ]);

        return $grade->fresh();
    }

    public function publishGrade(ExamGrade $grade, ?User $user = null, ?Doctor $doctor = null): ExamGrade
    {
        if (! in_array($grade->status, ['approved', 'auto_corrected', 'reviewed'], true)) {
            throw ValidationException::withMessages([
                'grade' => 'لا يمكن نشر الدرجة قبل اعتمادها أو إكمال مراجعتها.',
            ]);
        }

        $grade->forceFill([
            'status' => 'published',
            'published_at' => now(config('app.timezone', 'UTC')),
            'reviewed_by_doctor_id' => $doctor?->id ?? $grade->reviewed_by_doctor_id,
            'approved_by' => $user?->id ?? $grade->approved_by,
        ])->save();

        ExamGradeReview::query()->create([
            'grade_id' => $grade->id,
            'reviewer_user_id' => $user?->id,
            'reviewer_doctor_id' => $doctor?->id,
            'action' => 'published',
        ]);

        return $grade->fresh();
    }

    private function scoreAutoGradableAnswer(ExamAttemptAnswer $answer, $quizQuestion): float
    {
        $points = (float) $quizQuestion->points;
        $correctIds = $quizQuestion->choices->where('is_correct', true)->pluck('id')->sort()->values()->all();

        if ($quizQuestion->type_snapshot === 'single_choice') {
            $selected = $quizQuestion->choices->firstWhere('id', $answer->selected_choice_id);

            return ($selected && $selected->is_correct) ? $points : 0;
        }

        $selectedIds = collect($answer->selected_choice_ids ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();

        return $selectedIds === $correctIds ? $points : 0;
    }
}
