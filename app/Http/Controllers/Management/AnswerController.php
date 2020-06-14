<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Answers\AnswerList;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Services\Answer\AnswerService;
use App\Services\Input\InputFieldService;
use App\Services\Questions\QuestionService;
use Illuminate\Http\Request;


class AnswerController extends ApiController
{
    protected $questionService;
    protected $answersRepository;
    protected $inputFieldService;
    protected $answerService;
    protected $questionnaireSetsRepository;

    public function __construct(
        QuestionService $questionService,
        AnswersRepository $answersRepository,
        InputFieldService $inputFieldService,
        AnswerService $answerService,
        QuestionnaireSetsRepository $questionnaireSetsRepository
    ) {
        parent::__construct();
        $this->questionService = $questionService;
        $this->answersRepository = $answersRepository;
        $this->inputFieldService = $inputFieldService;
        $this->answerService = $answerService;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
    }

    public function getAnswerList()
    {
        return $this->runWithExceptionHandling(function () {
            $answers = $this->answersRepository->getAnswers();

            $this->response->setData(['data' => new AnswerList($answers)]);
        });
    }

    public function getAnswer(Request $request, $device_address, $set_id)
    {
        return $this->runWithExceptionHandling(function () use ($device_address, $set_id) {
            $answers = $this->answersRepository->getUserAnswersBySet($device_address, $set_id);

            $this->response->setData(['data' => $answers->toArray()]);
        });
    }

}