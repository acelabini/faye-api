<?php

namespace App\Http\Controllers;

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

class SummaryController extends ApiController
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
    )
    {
        parent::__construct();
        $this->questionService = $questionService;
        $this->answersRepository = $answersRepository;
        $this->inputFieldService = $inputFieldService;
        $this->answerService = $answerService;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
    }

    public function pieChart(Request $request)
    {

    }
}