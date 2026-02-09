<?php

namespace App\Voyager\Actions;

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
        return route('voyager.subjects.index', [
            'key'    => 'college_id',
            'filter' => 'equals',
            's'      => $this->data->id,
        ]);
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug === 'colleges';
    }
}
