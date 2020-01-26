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

    public function totalRespondents()
    {
        return $this->model
            ->select('device_address')
            ->groupBy('device_address')
            ->get()
            ->count()
            ;
    }

    public function getUserAnswersBySet($deviceAddress, $setId)
    {
        return $this->model
            ->selectRaw("
                answers.id as answer_id,
                answers.device_address,
                answers.questionnaire_id,
                answers.created_at as answer_date,
                questionnaire_sets.id,
                questionnaire_sets.set_id as set_id,
                question_sets.title as set_title,
                question_sets.description as set_description,
                questionnaire_sets.order as question_set_order,
                questions.title as question_title,
                questions.description as question_description,
                questions.id as question_id,
                answers.field_id,
                input_fields.label as input_label,
                answers.answer
            ")
            ->join('input_fields', 'input_fields.id', '=', 'answers.field_id')
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->join('question_sets', 'question_sets.id', '=', 'questionnaire_sets.set_id')
            ->join('questions', 'questions.id', '=', 'questionnaire_sets.question_id')
            ->where('answers.device_address', $deviceAddress)
            ->where('questionnaire_sets.set_id', $setId)
            ->orderBy('questionnaire_sets.order', 'asc')
            ->get();
    }

    public function getAnswers()
    {
        return $this->model
            ->selectRaw("
                answers.id as answer_id,
                answers.device_address,
                answers.questionnaire_id,
                answers.created_at,
                questionnaire_sets.id,
                questionnaire_sets.set_id as set_id,
                question_sets.title as set_title
            ")
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->join('question_sets', 'question_sets.id', '=', 'questionnaire_sets.set_id')
            ->whereNotNull('answers.device_address')
            ->groupBy('answers.device_address')
            ->groupBy('questionnaire_sets.set_id')
            ->orderBy('answers.created_at', 'desc')
            ->get()
            ;
    }

    public function countAnswered($setId = null, $deviceAddress = null)
    {
        return $this->model
            ->select('field_id', 'device_address')
            ->when($setId, function ($q) use ($setId) {
                $q->where('questionnaire_sets.set_id', $setId);
            })
            ->when($deviceAddress, function ($q) use ($deviceAddress) {
                $q->where('device_address', $deviceAddress);
            })
            ->join('questionnaire_sets', 'questionnaire_sets.id', '=', 'answers.questionnaire_id')
            ->whereNotNull('device_address')
            ->groupBy('answers.questionnaire_id')
            ->groupBy('answers.device_address')
            ->get()
            ;
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
                answers.device_address,
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

    public function getCategory($categories, $deviceAddress)
    {
        return $this->model
            ->where('device_address', $deviceAddress)
            ->where('answer', $categories)
            ->get();
    }
}
