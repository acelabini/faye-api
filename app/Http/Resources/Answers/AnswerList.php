<?php

namespace App\Http\Resources\Answers;

use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AnswerList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [];
        $questionnaireSet = app()->make(QuestionnaireSetsRepository::class);
        $answersRepo = app()->make(AnswersRepository::class);

        foreach ($this as $answers) {
            foreach ($answers as $answer) {
                $totalQuestions = $questionnaireSet->search([
                    ['set_id', $answer->set_id]
                ]);
                $result[] = [
                    'total_answered'    => $answersRepo->countAnswered($answer->set_id, $answer->device_address)->count(),
                    'total_questions'   => $totalQuestions->count(),
                    'device_address'    => $answer->device_address,
                    'set_id'            => $answer->set_id,
                    'set_title'         => $answer->set_title,
                    'created_at'        => $answer->created_at->format('M-d-Y H:i:s')
                ];
            }
        }

        return $result;
    }
}