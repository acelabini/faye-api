<?php

namespace App\Repositories;

use App\Models\Answers;
use App\Utils\Enumerators\SummaryTypeEnumerator;

class AnswersRepository extends Repository
{
    public function setModel()
    {
        $this->model = new Answers();
    }

    public function getSetAnswers($setId, $order = null)
    {
        return $this->model
            ->selectRaw('
                question_sets.id,
                questionnaire_sets.id,
                questionnaire_sets.set_id,
                answers.questionnaire_id,
                answers.answer,
                answers.field_id,
                input_fields.id,
                input_fields.label as input_label,
                input_fields.summary,
                questionnaire_sets.order,
                questions.id,
                questions.title as question_title,
                count(*) as total,
                sum(answers.answer) as answerSum,
                GROUP_CONCAT(answers.answer SEPARATOR ". ") as answer_concat
            ')
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->join('question_sets', 'question_sets.id', '=', 'questionnaire_sets.set_id')
            ->join('input_fields', 'input_fields.id', '=', 'answers.field_id')
            ->join('questions', 'questions.id', '=', 'questionnaire_sets.question_id')
            ->where('question_sets.id', $setId)
            ->whereNotNull('input_fields.summary')
            ->when($order, function ($q) use ($order) {
                $q->where('questionnaire_sets.order', $order);
            })
            ->groupBy('answers.answer')
            ->orderBy('questionnaire_sets.order', 'asc')
            ->get()
            ;
    }

    public function getPercentageAnswers($setId, $order = null)
    {
        return $this->model
            ->selectRaw('
                question_sets.id,
                questionnaire_sets.id,
                questionnaire_sets.set_id,
                answers.questionnaire_id,
                answers.answer,
                answers.field_id,
                input_fields.id,
                input_fields.label as input_label,
                input_fields.summary,
                questionnaire_sets.order,
                questions.id,
                questions.title as question_title,
                count(*) as total,
                sum(answers.answer) as answerSum
            ')
            ->join('input_fields', 'input_fields.id', '=', 'answers.field_id')
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->join('question_sets', 'question_sets.id', '=', 'questionnaire_sets.set_id')
            ->join('questions', 'questions.id', '=', 'questionnaire_sets.question_id')
            ->where('question_sets.id', $setId)
            ->where('input_fields.summary', SummaryTypeEnumerator::PERCENTAGE)
            ->when($order, function ($q) use ($order) {
                $q->where('questionnaire_sets.order', $order);
            })
            ->groupBy('answers.field_id')
            ->orderBy('questionnaire_sets.order', 'asc')
            ->get()
            ;
    }

    public function getCloudAnswers($setId, $order = null)
    {
        return $this->model
            ->selectRaw('
                question_sets.id,
                questionnaire_sets.id,
                questionnaire_sets.set_id,
                answers.questionnaire_id,
                answers.answer,
                answers.field_id,
                input_fields.id,
                input_fields.summary,
                questionnaire_sets.order,
                questions.id
            ')
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->join('question_sets', 'question_sets.id', '=', 'questionnaire_sets.set_id')
            ->join('input_fields', 'input_fields.id', '=', 'answers.field_id')
            ->join('questions', 'questions.id', '=', 'questionnaire_sets.question_id')
            ->where('question_sets.id', $setId)
            ->where('input_fields.summary', SummaryTypeEnumerator::CLOUD)
            ->when($order, function ($q) use ($order) {
                $q->where('questionnaire_sets.order', $order);
            })
            ->groupBy('answers.answer')
            ->orderBy('questionnaire_sets.order', 'asc')
            ->get()
            ;
    }
}
