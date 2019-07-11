<?php

namespace App\Http\Controllers;

use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Services\Answer\AnswerService;
use App\Services\Input\InputFieldService;
use App\Services\NLPService;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use App\Utils\Enumerators\SummaryTypeEnumerator;
use Illuminate\Http\Request;

class SummaryController extends ApiController
{
    protected $questionService;
    protected $answersRepository;
    protected $inputFieldService;
    protected $answerService;
    protected $questionnaireSetsRepository;
    protected $questionSetService;
    protected $questionSetsRepository;

    public function __construct(
        QuestionService $questionService,
        AnswersRepository $answersRepository,
        InputFieldService $inputFieldService,
        AnswerService $answerService,
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        QuestionSetService $questionSetService,
        QuestionSetsRepository $questionSetsRepository
    )
    {
        parent::__construct();
        $this->questionService = $questionService;
        $this->answersRepository = $answersRepository;
        $this->inputFieldService = $inputFieldService;
        $this->answerService = $answerService;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
        $this->questionSetService = $questionSetService;
        $this->questionSetsRepository = $questionSetsRepository;
    }

    public function summary(Request $request, $device = null, $order = null)
    {
        return $this->runWithExceptionHandling(function () use ($order, $device) {
            $data = [];
            $set = $this->questionSetService->getSet();
            $answers = $this->answersRepository->getSetAnswers($set->id, $order);
            $analysis = (new NLPService([
                'question_set'  =>  $set
            ]))->getAnswers()->LDA();
            $thematicAnalysis = $analysis->getWords();
            $cloud =
//            ''
                $analysis->topWords()
                ->generateCloud()->getWords()
            ;

            $percentage = $this->answersRepository->getPercentageAnswers($set->id, $order);

            foreach ($answers as $answer) {
                switch ($answer->summary) {
                    case SummaryTypeEnumerator::PIE:
                        //[$answer->questionnaire_id][$answer->field_id][$answer->input_label]
                        $data['pie'][$answer->questionnaire_id][$answer->input_label]['label'][] = $answer->answer;
                        $data['pie'][$answer->questionnaire_id][$answer->input_label]['data'][] = $answer->total;
                        $data['pie'][$answer->questionnaire_id][$answer->input_label]['backgroundColor'][] = $this->generateColor();
                        break;
                    case SummaryTypeEnumerator::BAR:
                        $data['bar'][$answer->questionnaire_id][$answer->input_label]['label'][] = $answer->answer;
                        $data['bar'][$answer->questionnaire_id][$answer->input_label]['data'][] = $answer->total;
                        $data['bar'][$answer->questionnaire_id][$answer->input_label]['backgroundColor'][] =
                            $this->generateColor(true);
                        break;
                    default:
                        break;
                }
            }

            foreach ($percentage as $percent) {
                $myAnswer = $this->answersRepository->search([
                    ['device_address', $device],
                    ['questionnaire_id', $percent->questionnaire_id],
                    ['field_id', $percent->field_id],
                ])->first();
                $data['percentage'][$percent->questionnaire_id][$percent->input_label] = [
                    'label'     =>  $percent->label,
                    'respondents'   =>  number_format($percent->total),
                    'data'      =>  number_format($percent->answerSum / $percent->total, 2),
                    'answer'    =>  optional($myAnswer)->answer
                ];
            }

            $data['cloud'] = $cloud;
            $data['thematics_analysis'] = $thematicAnalysis;

            $this->response->setData(['data' => $data]);
        });
    }

    protected function generateColor($rgba = false)
    {
        if ($rgba) {
            $hash = md5('color' . rand(0, 20));
            $r = hexdec(substr($hash, 0, 2));
            $g = hexdec(substr($hash, 2, 2));
            $b = hexdec(substr($hash, 4, 2));

            return "$r, $g, $b";
        }

        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function wordCloud()
    {

    }
}