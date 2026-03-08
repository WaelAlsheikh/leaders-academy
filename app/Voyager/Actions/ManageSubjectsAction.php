<?php

namespace App\Voyager\Actions;

use App\Models\RegistrableEntity;
use TCG\Voyager\Actions\AbstractAction;

class ManageSubjectsAction extends AbstractAction
{
    public function getTitle()
    {
        return 'إدارة المواد';
    }

    public function getIcon()
    {
        return 'voyager-book';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary',
        ];
    }

    public function getDefaultRoute()
    {
        if ($this->dataType->slug === 'program-branches') {
            $entityId = RegistrableEntity::query()
                ->where('entity_type', 'program_branch')
                ->where('entity_id', $this->data->id)
                ->value('id');

            return $entityId
                ? route('admin.registrables.subjects', $entityId)
                : '#';
        }

        if ($this->dataType->slug === 'training-program-branches') {
            $entityId = RegistrableEntity::query()
                ->where('entity_type', 'training_program_branch')
                ->where('entity_id', $this->data->id)
                ->value('id');

            return $entityId
                ? route('admin.registrables.subjects', $entityId)
                : '#';
        }

        return route('voyager.subjects.index', [
            'key'    => 'college_id',
            'filter' => 'equals',
            's'      => $this->data->id,
        ]);
    }

    public function shouldActionDisplayOnDataType()
    {
        return in_array($this->dataType->slug, [
            'colleges',
            'program-branches',
            'training-program-branches',
        ], true);
    }
}
