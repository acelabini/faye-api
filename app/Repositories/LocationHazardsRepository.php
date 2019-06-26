<?php

namespace App\Repositories;

use App\Models\LocationHazards;

class LocationHazardsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new LocationHazards();
    }
}
