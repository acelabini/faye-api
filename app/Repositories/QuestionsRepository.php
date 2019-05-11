<?php

namespace App\Repositories;

use App\Models\Questions;

class QuestionsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new Questions();
    }
}
