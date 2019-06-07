<?php

namespace App\Services\Answer;

use App\Models\QuestionnaireSets;
use App\Models\Questions;
use App\Models\QuestionSets;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnswerService
{
    protected $questionnaireSetsRepository;
    protected $answersRepository;

    public function __construct(
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        AnswersRepository $answersRepository
    ) {
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
        $this->answersRepository = $answersRepository;
    }

    public static function getAnswerIdentifier($cookie) : array
    {
        $identifier['user_id'] = optional(Auth::user())->id;
        $identifier['device_address'] = $cookie;

        return $identifier;
    }

    public function answerQuestion(QuestionnaireSets $currentSet, $identifier, array $data)
    {
        foreach ($data as $datum) {
            $dataPosted = [
                    'questionnaire_id'  =>  $currentSet->id,
                    'field_id'          =>  $datum['id'],
                    'answer'            =>  $datum['answer']
            ];
            $searched = $this->answersRepository->search([
                ['questionnaire_id', $currentSet->id],
                ['field_id', $datum['id']]
            ]);
            if ($searched->isNotEmpty()) {
                $this->answersRepository->update($searched->first(), $dataPosted);
            } else {
                $this->answersRepository->create(
                    array_merge(
                        $identifier,
                        $dataPosted
                    )
                );
            }
        }
    }
}