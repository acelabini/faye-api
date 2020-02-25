<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Resources\Questions\QuestionCreation;
use App\Http\Resources\Sets\GetQuestionSets;
use App\Http\Resources\Sets\QuestionSetsResource;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Repositories\UserRepository;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class QuestionnaireController extends ApiController
{
    protected $questionService;
    protected $questionsRepository;
    protected $questionSetService;
    protected $questionSetsRepository;
    protected $questionnaireSetsRepository;
    protected $userRepository;

    public function __construct(
        QuestionService $questionService,
        QuestionSetService $questionSetService,
        QuestionsRepository $questionsRepository,
        QuestionSetsRepository $questionSetsRepository,
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->questionService = $questionService;
        $this->questionSetService = $questionSetService;
        $this->questionsRepository = $questionsRepository;
        $this->questionSetsRepository = $questionSetsRepository;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionnaire($order, $setId = null)
    {
        return $this->runWithExceptionHandling(function () use ($order, $setId) {
            $order = intval($order);
            list($currentQuestion, $currentSet) = $this->questionService->getCurrentQuestion(
                null,
                $order,
                $setId
            );
            if (!$currentQuestion) {
                throw new ApiException(
                    "You've finished all the questions. Thank you for your time",
                    Response::HTTP_OK
                );
            }
            if ($order > $currentSet->order && !env('SKIP_QUESTIONS')) {
                throw new UnauthorizedException("Unauthorized access.", Response::HTTP_UNAUTHORIZED);
            }

            $this->response->setData(['data' => new QuestionCreation($currentQuestion)]);
        });
    }

    public function getDefaultSet()
    {
        return $this->runWithExceptionHandling(function () {
            $set = $this->questionSetService->getSet();

            $this->response->setData(['data' => ['set' => $set, 'questions_count' => $set->questionnaires->count()]]);
        });
    }

    public function getSets()
    {
        return $this->runWithExceptionHandling(function () {
            $sets = $this->questionSetsRepository->getActiveSets();

            $this->response->setData(['data' => ['sets' => $sets]]);
        });
    }

    public function getSet($id)
    {
        return $this->runWithExceptionHandling(function () use ($id) {
            $set = $this->questionSetsRepository->get($id);

            $this->response->setData(['data' => ['set' => $set, 'questions_count' => $set->questionnaires->count()]]);
        });
    }
}
