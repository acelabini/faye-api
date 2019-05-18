<?php

namespace App\Services\Answer;

use App\Models\QuestionnaireSets;
use App\Models\Questions;
use App\Models\QuestionSets;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use Illuminate\Support\Facades\Auth;
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

    public static function getAnswerIdentifier($returnCookie = false) : array
    {
        $identifier['user_id'] = optional(Auth::user())->id;
        $identifier['device_address'] = null;
        $cookie = null;
        if (!Auth::check() && !request()->hasCookie('device')) {
            $address = Str::random(16);
            $cookie = cookie('device', $address, time() + 60 * 60 * 24 * 365);
            $identifier['device_address'] = $address;
        } else if (!Auth::check() && request()->hasCookie('device')){
            $identifier['device_address'] = request()->cookie('device');
        }

        return $returnCookie ? [$identifier, $cookie] : $identifier;
    }

    public function answerQuestion(QuestionnaireSets $currentSet, $identifier, array $data)
    {
        foreach ($data as $datum) {
            $this->answersRepository->create(
                array_merge(
                    $identifier,
                    [
                        'questionnaire_id'  =>  $currentSet->id,
                        'field_id'          =>  $datum['id'],
                        'answer'            =>  $datum['answer']
                    ]
                )
            );
        }
    }
}