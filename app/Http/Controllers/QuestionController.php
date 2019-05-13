<?php

namespace App\Http\Controllers;

use App\Http\Resources\Questions\GetQuestions;
use App\Http\Resources\Questions\QuestionCreation;
use App\Http\Resources\Questions\QuestionSetsResource;
use App\Repositories\QuestionsRepository;
use App\Repositories\UserRepository;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use App\Validations\QuestionCreate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends ApiController
{
    protected $questionService;
    protected $questionsRepository;
    protected $questionSetService;
    protected $userRepository;

    public function __construct(
        QuestionService $questionService,
        QuestionSetService $questionSetService,
        QuestionsRepository $questionsRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->questionService = $questionService;
        $this->questionSetService = $questionSetService;
        $this->questionsRepository = $questionsRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * {
     *  "title": "Tell me everything?",
     *  "description": "Answer at least three",
     *  "inputs": [
     *          {
     *          "type_id": 1,
     *          "name": "text_box",
     *          "label": "Text Box",
     *          "description": "",
     *          "validations": {
     *                  "min": 1,
     *                  "max": 25,
     *                  "required": true
     *              },
     *          "options": {
     *                  "value": "",
     *                  "placeholder": "Text Box"
     *              }
     *          },
     *          {
     *          "type_id": 3,
     *          "name": "select",
     *          "label": "Select type",
     *          "description": "",
     *          "validations": {
     *                  "required": true
     *              },
     *          "options": {
     *                  "value": "1"
     *              },
     *         "select_options": {
     *                 "1": "One",
     *                 "2": "Two",
     *                 "3": "Three"
     *             }
     *         }
     *     ]
     * }
     */
    public function createQuestion(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, QuestionCreate::getRules(), QuestionCreate::getMessages());

            $user = $this->userRepository->get(Auth::user()->id);
            $question = $this->questionService->create($user, [
                'title'         =>  $request->get('title'),
                'description'   =>  $request->get('description'),
                'inputs'        =>  $request->get('inputs')
            ]);

            $this->response->setData(['data' => new QuestionCreation($question)]);
        });
    }

    /**
     * Get all questions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestions()
    {
        return $this->runWithExceptionHandling(function () {
            $questions = $this->questionsRepository->all();

            $this->response->setData(['data' => new GetQuestions($questions)]);
        });
    }

    /**
     * Get question using id
     *
     * @param $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestion($questionId)
    {
        return $this->runWithExceptionHandling(function () use ($questionId) {
            $question = $this->questionsRepository->get($questionId);

            $this->response->setData(['data' => new QuestionCreation($question)]);
        });
    }
}