<?php

namespace App\Repositories;

use App\Models\QuestionSets;

class QuestionSetsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new QuestionSets();
    }
}
