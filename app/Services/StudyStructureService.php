<?php

namespace App\Services;

use App\Models\RegistrableEntity;
use App\Models\StudyTerm;
use App\Models\StudyYear;

class StudyStructureService
{
    /**
     * @return array{year: \App\Models\StudyYear, term: \App\Models\StudyTerm}
     */
    public function ensureDefaultStructureForEntity(RegistrableEntity $entity): array
    {
        $studyYear = StudyYear::firstOrCreate(
            [
                'registrable_entity_id' => $entity->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'السنة الأولى',
            ]
        );

        $studyTerm = StudyTerm::firstOrCreate(
            [
                'study_year_id' => $studyYear->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'الفصل الأول',
                'code' => null,
            ]
        );

        return [
            'year' => $studyYear,
            'term' => $studyTerm,
        ];
    }

    public function getDefaultTermForEntity(RegistrableEntity $entity): StudyTerm
    {
        return $this->ensureDefaultStructureForEntity($entity)['term'];
    }
}
