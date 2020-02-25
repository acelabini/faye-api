<?php

namespace App\Repositories;

use App\Models\QuestionnaireSets;
use App\Models\QuestionSets;

class QuestionnaireSetsRepository extends Repository
{
    public function setModel()
    {
        $this->model = new QuestionnaireSets();
    }

    public function getQuestionBySetAndOrder(QuestionSets $questionSet, $orderId)
    {
        return $this->model->where('set_id', $questionSet->id)
            ->where('order', $orderId)
            ->first();
    }

    public function deleteQuestionnaire(QuestionSets $questionSets, array $questionIds)
    {
        return $this->model
            ->where('set_id', $questionSets->id)
            ->whereIn('question_id', $questionIds)
            ->delete();
    }
}
