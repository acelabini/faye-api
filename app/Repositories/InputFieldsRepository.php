<?php

namespace App\Repositories;

use App\Models\InputFields;

class InputFieldsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new InputFields();
    }

    public function getInputs(array $ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function removeInputs(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->delete();
    }
}
