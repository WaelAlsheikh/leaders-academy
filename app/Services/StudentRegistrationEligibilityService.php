<?php

namespace App\Services;

use App\Models\EnrollmentCycle;
use App\Models\RegistrableEntity;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudyTerm;
use Illuminate\Support\Collection;

class StudentRegistrationEligibilityService
{
    public function eligibleSubjectsForCycle(Student $student, EnrollmentCycle $cycle): Collection
    {
        $cycle->loadMissing([
            'registrableEntity.studyTerms.studyYear',
            'registrableSubjects' => fn ($query) => $query
                ->wherePivot('is_open', true)
                ->where('is_active', true)
                ->with('studyTerm.studyYear'),
        ]);

        $entity = $cycle->registrableEntity;
        if (!$entity) {
            return collect();
        }

        $openSubjects = $cycle->registrableSubjects
            ->filter(fn ($subject) => $subject->study_term_id !== null)
            ->values();

        if ($openSubjects->isEmpty()) {
            return collect();
        }

        $acceptedRegistrations = Registration::query()
            ->with(['registrableSubjects' => fn ($query) => $query->with('studyTerm.studyYear')])
            ->where('student_id', $student->id)
            ->where('registrable_entity_id', $entity->id)
            ->where('status', 'accepted')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($acceptedRegistrations->isEmpty()) {
            return $this->firstTermSubjects($entity, $openSubjects);
        }

        $latestResultsBySubject = [];
        $attemptedTermIds = [];

        foreach ($acceptedRegistrations as $registration) {
            foreach ($registration->registrableSubjects as $subject) {
                if (!$subject->study_term_id) {
                    continue;
                }

                $latestResultsBySubject[$subject->id] = $subject->pivot->result_status ?? 'undefined';
                $attemptedTermIds[$subject->study_term_id] = true;
            }
        }

        $orderedTerms = $entity->studyTerms()
            ->with('studyYear')
            ->orderBy('study_years.sort_order')
            ->orderBy('study_terms.sort_order')
            ->orderBy('study_terms.id')
            ->get();

        if ($orderedTerms->isEmpty()) {
            return collect();
        }

        $orderedTermIds = $orderedTerms->pluck('id')->values()->all();
        $highestAttemptedIndex = -1;

        foreach ($orderedTermIds as $index => $termId) {
            if (isset($attemptedTermIds[$termId])) {
                $highestAttemptedIndex = $index;
            }
        }

        if ($highestAttemptedIndex < 0) {
            return $this->firstTermSubjects($entity, $openSubjects);
        }

        $nextTermId = $orderedTermIds[$highestAttemptedIndex + 1] ?? null;

        $carryOverSubjects = $openSubjects->filter(function ($subject) use ($latestResultsBySubject, $attemptedTermIds) {
            if (!isset($attemptedTermIds[$subject->study_term_id])) {
                return false;
            }

            return ($latestResultsBySubject[$subject->id] ?? 'undefined') !== 'passed';
        });

        $nextTermSubjects = $openSubjects->filter(function ($subject) use ($nextTermId, $latestResultsBySubject) {
            if ($nextTermId === null || $subject->study_term_id !== $nextTermId) {
                return false;
            }

            return ($latestResultsBySubject[$subject->id] ?? 'undefined') !== 'passed';
        });

        return $carryOverSubjects
            ->merge($nextTermSubjects)
            ->unique('id')
            ->sortBy([
                fn ($subject) => $subject->studyTerm?->studyYear?->sort_order ?? 999,
                fn ($subject) => $subject->studyTerm?->sort_order ?? 999,
                fn ($subject) => $subject->name,
            ])
            ->values();
    }

    public function groupSubjectsByPlan(Collection $subjects): Collection
    {
        return $subjects
            ->groupBy(fn ($subject) => $subject->studyTerm?->studyYear?->id ?? 'ungrouped')
            ->map(function ($yearSubjects) {
                $studyYear = $yearSubjects->first()?->studyTerm?->studyYear;

                return [
                    'study_year' => $studyYear,
                    'terms' => $yearSubjects
                        ->groupBy(fn ($subject) => $subject->studyTerm?->id ?? 'ungrouped')
                        ->map(function ($termSubjects) {
                            $studyTerm = $termSubjects->first()?->studyTerm;

                            return [
                                'study_term' => $studyTerm,
                                'subjects' => $termSubjects->sortBy('name')->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }

    private function firstTermSubjects(RegistrableEntity $entity, Collection $openSubjects): Collection
    {
        $firstTermId = $entity->studyTerms()
            ->orderBy('study_years.sort_order')
            ->orderBy('study_terms.sort_order')
            ->orderBy('study_terms.id')
            ->value('study_terms.id');

        if (!$firstTermId) {
            return collect();
        }

        return $openSubjects
            ->where('study_term_id', $firstTermId)
            ->sortBy('name')
            ->values();
    }
}
