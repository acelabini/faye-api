<?php

namespace App\Repositories;

use App\Models\Hazards;

class HazardsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new Hazards();
    }
}
