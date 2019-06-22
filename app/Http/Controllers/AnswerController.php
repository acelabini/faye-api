<?php

namespace App\Http\Controllers;

use App\Http\Resources\Answers\AnswerList;
use App\Http\Resources\Answers\GetAnswer;
use App\Http\Resources\Sets\GetQuestionSets;
use App\Http\Resources\Sets\QuestionSetsResource;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Repositories\UserRepository;
use App\Services\Answer\AnswerService;
use App\Services\Input\InputFieldService;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

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

    /**
     * {
     *          "range_date": {
     *              "id" : 1,
     *              "answer": "9"
     *          },
     *          "select_gender": {
     *              "id" : 2,
     *              "answer": "Male"
     *          }
     * }
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function answer(Request $request, $order)
    {
        return $this->runWithExceptionHandling(function () use ($request, $order) {
            list($currentQuestion, $currentSet) = $this->questionService->getCurrentQuestion(null, $order);
            if ($currentQuestion) {
                $this->validate($request, $this->inputFieldService->validateInputFields($currentQuestion->inputs));
                $fields = $currentQuestion->inputs->pluck('name')->toArray();
                $answers = $request->all();
                $identifier = AnswerService::getAnswerIdentifier($answers['device']);
                unset($answers['device']);
                $data = [];
                foreach ($fields as $field) {
                    if (isset($answers[$field])) {
                        $data[] = $answers[$field];
                    }
                }
                $this->answerService->answerQuestion($currentSet, $identifier, $data);
            }

            $this->response->setData(['data' => ['success' => true, 'finish' => !$currentQuestion]]);
        });
    }

    public function getAnswer(Request $request, $order)
    {
        return $this->runWithExceptionHandling(function () use ($request, $order) {
            $order = intval($order);
            list($currentQuestion, $currentSet) = $this->questionService->getCurrentQuestion(null, $order);
            $identifier = AnswerService::getAnswerIdentifier($request->get('device'));
            $answers = $this->answersRepository->search([
                ['user_id', $identifier['user_id']],
                ['device_address', $identifier['device_address']],
                ['questionnaire_id', $currentSet->id]
            ]);
//            if ($order > $currentSet->order) {
//                throw new UnauthorizedException("Unauthorized access.", Response::HTTP_UNAUTHORIZED);
//            }

            $this->response->setData(['data' => new GetAnswer($answers)]);
        });
    }

    public function getAnswerList()
    {
        return $this->runWithExceptionHandling(function () {
            $answers = $this->answersRepository->getAnswers();

            $this->response->setData(['data' => new AnswerList($answers)]);
        });
    }
}
