<?php

namespace App\Repositories;

use App\Models\InputFields;

class InputFieldsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new InputFields();
    }
}
