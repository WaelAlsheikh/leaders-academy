<?php

namespace App\Services;

use App\Models\College;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class CollegeSubjectSyncService
{
    public function __construct(
        private readonly StudyStructureService $studyStructureService
    ) {
    }

    public function ensureCollegeEntity(College $college): RegistrableEntity
    {
        $entity = RegistrableEntity::updateOrCreate(
            ['entity_type' => 'college', 'entity_id' => $college->id],
            [
                'title_snapshot' => $college->title,
                'price_per_credit_hour' => $college->price_per_credit_hour ?? 0,
                'is_active' => true,
            ]
        );

        $this->studyStructureService->ensureDefaultStructureForEntity($entity);

        return $entity;
    }

    public function syncLegacySubject(Subject $subject, ?int $doctorId = null): RegistrableSubject
    {
        $subject->loadMissing('college');

        $entity = $this->ensureCollegeEntity($subject->college);
        $registrableSubject = RegistrableSubject::firstOrNew([
            'legacy_subject_id' => $subject->id,
        ]);

        $registrableSubject->registrable_entity_id = $entity->id;
        $registrableSubject->study_term_id = $subject->study_term_id ?: $this->studyStructureService->getDefaultTermForEntity($entity)->id;
        $registrableSubject->name = $subject->name;
        $registrableSubject->code = $subject->code;
        $registrableSubject->credit_hours = $subject->credit_hours;
        $registrableSubject->is_active = (bool) $subject->is_active;

        if (func_num_args() > 1) {
            $registrableSubject->doctor_id = $doctorId;
        }

        $registrableSubject->save();

        return $registrableSubject;
    }

    public function createLegacyAndSync(College $college, array $attributes): RegistrableSubject
    {
        return DB::transaction(function () use ($college, $attributes) {
            $subject = $college->subjects()->create([
                'study_term_id' => $attributes['study_term_id'] ?? $this->studyStructureService->getDefaultTermForEntity($this->ensureCollegeEntity($college))->id,
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'credit_hours' => $attributes['credit_hours'],
                'is_active' => $attributes['is_active'] ?? true,
            ]);

            return $this->syncLegacySubject($subject, $attributes['doctor_id'] ?? null);
        });
    }

    public function updateLegacyAndSync(Subject $subject, array $attributes): RegistrableSubject
    {
        return DB::transaction(function () use ($subject, $attributes) {
            $subject->update([
                'study_term_id' => $attributes['study_term_id'] ?? $subject->study_term_id,
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'credit_hours' => $attributes['credit_hours'],
                'is_active' => $attributes['is_active'] ?? true,
            ]);

            return $this->syncLegacySubject($subject->fresh(), $attributes['doctor_id'] ?? null);
        });
    }

    public function deleteLegacyAndSyncedSubject(Subject $subject): void
    {
        DB::transaction(function () use ($subject) {
            $subject->registrableSubject?->delete();
            $subject->delete();
        });
    }
}
