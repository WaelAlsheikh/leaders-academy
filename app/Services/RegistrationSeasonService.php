<?php

namespace App\Services;

use App\Models\EnrollmentCycle;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\RegistrationSeason;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationSeasonService
{
    public function createSeason(array $attributes, Collection $entities, ?int $actorId = null): RegistrationSeason
    {
        return DB::transaction(function () use ($attributes, $entities, $actorId) {
            $season = RegistrationSeason::create([
                'name' => $attributes['name'],
                'code' => $attributes['code'] ?: null,
                'registration_starts_at' => $attributes['registration_starts_at'] ?? null,
                'registration_ends_at' => $attributes['registration_ends_at'] ?? null,
                'status' => $attributes['status'] ?? 'open',
                'created_by' => $actorId,
            ]);

            foreach ($entities as $entity) {
                $this->enableEntity($season, $entity, $actorId);
            }

            return $season->fresh('enabledEnrollmentCycles');
        });
    }

    public function syncSeason(RegistrationSeason $season, ?int $actorId = null): void
    {
        $season->loadMissing('enrollmentCycles.semester');

        foreach ($season->enrollmentCycles as $cycle) {
            $this->syncCycleMetadata($season, $cycle, $actorId);
        }
    }

    public function enableEntity(RegistrationSeason $season, RegistrableEntity $entity, ?int $actorId = null): EnrollmentCycle
    {
        $cycle = EnrollmentCycle::firstOrNew([
            'registration_season_id' => $season->id,
            'registrable_entity_id' => $entity->id,
        ]);

        $cycle->college_id = $entity->entity_type === 'college' ? $entity->entity_id : null;
        $cycle->name = $season->name;
        $cycle->code = $season->code;
        $cycle->registration_starts_at = $season->registration_starts_at;
        $cycle->registration_ends_at = $season->registration_ends_at;
        $cycle->status = $season->status === 'open' ? 'open' : 'closed';
        $cycle->is_enabled = true;
        $cycle->created_by = $cycle->created_by ?: $actorId;
        $cycle->save();

        $this->syncCycleSubjects($cycle);
        $this->ensureSemester($season, $cycle, $actorId);

        return $cycle->fresh(['registrableEntity', 'semester']);
    }

    public function disableEntity(RegistrationSeason $season, RegistrableEntity $entity): ?EnrollmentCycle
    {
        $cycle = EnrollmentCycle::query()
            ->where('registration_season_id', $season->id)
            ->where('registrable_entity_id', $entity->id)
            ->first();

        if (!$cycle) {
            return null;
        }

        $cycle->update([
            'is_enabled' => false,
            'status' => 'closed',
        ]);

        return $cycle;
    }

    public function syncSemesterSubjectsForCycle(EnrollmentCycle $cycle): void
    {
        if (!$cycle->semester) {
            return;
        }

        $cycle->loadMissing('registrableSubjects');

        $syncData = [];
        foreach ($cycle->registrableSubjects as $subject) {
            $syncData[$subject->id] = [
                'is_active' => true,
                'registered_count' => 0,
                'subject_id' => $subject->legacy_subject_id,
            ];
        }

        $cycle->semester->registrableSubjects()->sync($syncData);
    }

    public function syncOpenSeasonSubjectsForEntity(RegistrableEntity $entity): void
    {
        $cycles = EnrollmentCycle::query()
            ->with(['semester', 'registrationSeason'])
            ->where('registrable_entity_id', $entity->id)
            ->where('is_enabled', true)
            ->activeListing()
            ->whereHas('registrationSeason', function ($query) {
                $query->where('status', 'open');
            })
            ->get();

        foreach ($cycles as $cycle) {
            $this->syncCycleSubjects($cycle);

            if ($cycle->semester) {
                $this->syncSemesterSubjectsForCycle($cycle->fresh(['semester', 'registrableSubjects']));
            }
        }
    }

    private function syncCycleMetadata(RegistrationSeason $season, EnrollmentCycle $cycle, ?int $actorId = null): void
    {
        $cycle->update([
            'name' => $season->name,
            'code' => $season->code,
            'registration_starts_at' => $season->registration_starts_at,
            'registration_ends_at' => $season->registration_ends_at,
            'status' => $season->status === 'open' && $cycle->is_enabled ? 'open' : 'closed',
        ]);

        if ($cycle->is_enabled) {
            $this->syncCycleSubjects($cycle);
            $this->ensureSemester($season, $cycle, $actorId);
        } elseif ($cycle->semester) {
            $this->syncSemesterMetadata($season, $cycle);
        }
    }

    private function syncCycleSubjects(EnrollmentCycle $cycle): void
    {
        $existingSubjectIds = $cycle->registrableSubjects()
            ->pluck('registrable_subjects.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $activeSubjects = RegistrableSubject::query()
            ->where('registrable_entity_id', $cycle->registrable_entity_id)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $attachData = [];
        foreach (array_diff($activeSubjects, $existingSubjectIds) as $subjectId) {
            $attachData[$subjectId] = ['is_open' => true];
        }

        if ($attachData !== []) {
            $cycle->registrableSubjects()->syncWithoutDetaching($attachData);
        }
    }

    private function ensureSemester(RegistrationSeason $season, EnrollmentCycle $cycle, ?int $actorId = null): void
    {
        $semester = $cycle->semester ?: new Semester();

        if (!$semester->exists) {
            $semester->enrollment_cycle_id = $cycle->id;
            $semester->created_by = $semester->created_by ?: $actorId;
        }

        $semester->college_id = $cycle->college_id;
        $semester->name = $this->buildSemesterName($season, $cycle->registrableEntity);
        $semester->code = $this->buildSemesterCode($season, $cycle->registrableEntity, $semester);
        $semester->start_date = optional($season->registration_starts_at)->toDateString() ?? now()->toDateString();
        $semester->end_date = optional($season->registration_ends_at)->toDateString();
        $semester->status = 'active';
        $semester->save();

        $this->syncSemesterSubjectsForCycle($cycle->fresh(['semester', 'registrableSubjects']));
    }

    private function syncSemesterMetadata(RegistrationSeason $season, EnrollmentCycle $cycle): void
    {
        if (!$cycle->semester) {
            return;
        }

        $cycle->semester->update([
            'name' => $this->buildSemesterName($season, $cycle->registrableEntity),
            'code' => $this->buildSemesterCode($season, $cycle->registrableEntity, $cycle->semester),
            'start_date' => optional($season->registration_starts_at)->toDateString() ?? $cycle->semester->start_date,
            'end_date' => optional($season->registration_ends_at)->toDateString(),
        ]);
    }

    private function buildSemesterName(RegistrationSeason $season, ?RegistrableEntity $entity): string
    {
        if (!$entity) {
            return $season->name;
        }

        return trim($season->name . ' - ' . $entity->display_title, ' -');
    }

    private function buildSemesterCode(RegistrationSeason $season, ?RegistrableEntity $entity, Semester $semester): string
    {
        $baseCode = trim((string) ($season->code ?: 'SEASON-' . $season->id));
        $entitySuffix = $entity
            ? Str::upper(match ($entity->entity_type) {
                'college' => 'C',
                'program_branch' => 'P',
                'training_program_branch' => 'T',
                default => 'E',
            }) . $entity->id
            : 'E0';

        $baseCandidate = Str::limit($baseCode . '-' . $entitySuffix, 50, '');
        $candidate = $baseCandidate;
        $counter = 2;

        while (Semester::query()
            ->where('code', $candidate)
            ->when($semester->exists, fn ($query) => $query->where('id', '!=', $semester->id))
            ->exists()
        ) {
            $suffix = '-' . $counter;
            $candidate = Str::limit($baseCandidate, 50 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $candidate;
    }
}
