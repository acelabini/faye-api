<?php

namespace App\Repositories;

use App\Models\InputFieldType;

class InputFieldTypeRepository extends Repository
{
    public function setModel()
    {
        $this->model = new InputFieldType();
    }
}
