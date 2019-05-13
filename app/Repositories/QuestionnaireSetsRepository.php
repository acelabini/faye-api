<?php

namespace App\Repositories;

use App\Models\QuestionnaireSets;

class QuestionnaireSetsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new QuestionnaireSets();
    }
}
