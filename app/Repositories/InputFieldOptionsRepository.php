<?php

namespace App\Repositories;

use App\Models\InputFieldOptions;

class InputFieldOptionsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new InputFieldOptions();
    }
}
