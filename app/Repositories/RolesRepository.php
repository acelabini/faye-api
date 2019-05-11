<?php

namespace App\Repositories;

use App\Models\Roles;

class RolesRepository extends Repository
{
    public function setModel()
    {
        $this->model = new Roles();
    }
}
