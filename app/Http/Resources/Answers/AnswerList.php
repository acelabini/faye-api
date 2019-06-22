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
        $x = $answersRepo->countAnswered()->toArray();
Log::info('', array_column($x, 1));
//Log::info('', array_count_values(array_column($x, 1)));
        foreach ($this as $answers) {
            foreach ($answers as $answer) {
                $totalQuestions = $questionnaireSet->search([
                    ['set_id', $answer->set_id]
                ]);
//                $totalAnswered = $answersRepo->search([
//                    ['device_address', $answer->device_address],
//                    ['device_address', $answer->device_address]
//                ]);
                $result[] = [
//                    'total_answered' => $totalAnswered->count(),
                    'total_questions' => $totalQuestions->count(),
                    'device_address' => $answer->device_address,
                    'set_id' => $answer->set_id,
                    'created_at' => $answer->created_at->format('Y-m-d H:i:s')
                ];
            }
        }

        return $result;
    }
}