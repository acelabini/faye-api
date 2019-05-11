<?php

namespace App\Repositories;

use App\Models\Answers;

class AnswersRepository extends Repository
{
    public function setModel()
    {
        $this->model = new Answers();
    }
}
