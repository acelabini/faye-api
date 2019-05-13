<?php

namespace App\Http\Controllers;

use App\Http\Resources\Sets\GetQuestionSets;
use App\Http\Resources\Sets\QuestionSetsResource;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Repositories\UserRepository;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetController extends ApiController
{
    protected $questionService;
    protected $questionsRepository;
    protected $questionSetService;
    protected $questionSetsRepository;
    protected $userRepository;

    public function __construct(
        QuestionService $questionService,
        QuestionSetService $questionSetService,
        QuestionsRepository $questionsRepository,
        QuestionSetsRepository $questionSetsRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->questionService = $questionService;
        $this->questionSetService = $questionSetService;
        $this->questionsRepository = $questionsRepository;
        $this->questionSetsRepository = $questionSetsRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * {
     *       "title": "Set one",
     *       "description":	"About one",
     *       "question_ids": {
     *               "0": 1,
     *               "1": 2
     *           }
     *   }
     */
    public function createSet(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'title'         =>  'required',
                'question_ids'  =>  'required'
            ]);

            $user = $this->userRepository->get(Auth::user()->id);
            $set = $this->questionSetService->create($user, $request->only(['title', 'description', 'question_ids']));

            $this->response->setData(['data' => new QuestionSetsResource($set)]);
        });
    }

    public function getQuestionSets()
    {
        return $this->runWithExceptionHandling(function () {
            $sets = $this->questionSetsRepository->all();

            $this->response->setData(['data' => new GetQuestionSets($sets)]);
        });
    }

    public function getQuestionSet($setId)
    {
        return $this->runWithExceptionHandling(function () use ($setId) {
            $set = $this->questionSetsRepository->get($setId);

            $this->response->setData(['data' => new QuestionSetsResource($set)]);
        });
    }
}