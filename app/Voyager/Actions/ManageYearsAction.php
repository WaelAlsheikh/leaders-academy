<?php

namespace App\Voyager\Actions;

use App\Models\RegistrableEntity;
use TCG\Voyager\Actions\AbstractAction;

class ManageYearsAction extends AbstractAction
{
    public function getTitle()
    {
        return 'إدارة السنوات';
    }

    public function getIcon()
    {
        return 'voyager-layers';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary',
        ];
    }

    public function getDefaultRoute()
    {
        RegistrableEntity::syncFromSources();

        if ($this->dataType->slug === 'program-branches') {
            $entityId = RegistrableEntity::query()
                ->where('entity_type', 'program_branch')
                ->where('entity_id', $this->data->id)
                ->value('id');

            return $entityId
                ? route('admin.registrables.years', $entityId)
                : '#';
        }

        if ($this->dataType->slug === 'training-program-branches') {
            $entityId = RegistrableEntity::query()
                ->where('entity_type', 'training_program_branch')
                ->where('entity_id', $this->data->id)
                ->value('id');

            return $entityId
                ? route('admin.registrables.years', $entityId)
                : '#';
        }

        return route('admin.colleges.years', $this->data->id);
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
