<?php

namespace App\Repositories;

use App\Models\QuestionSets;
use App\Utils\Enumerators\QuestionSetStatusEnumerator;

class QuestionSetsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new QuestionSets();
    }
}
