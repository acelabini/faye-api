<?php

namespace App\Http\Controllers;

use App\Models\LocationBarangays;
use App\Models\QuestionSets;
use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use App\Repositories\ProcessedDataRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Services\Answer\AnswerService;
use App\Services\Input\InputFieldService;
use App\Services\LDAService;
use App\Services\NLPService;
use App\Services\Questions\QuestionService;
use App\Sets\QuestionSetService;
use App\Utils\Enumerators\SummaryTypeEnumerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SummaryController extends ApiController
{
    protected $questionService;
    protected $answersRepository;
    protected $inputFieldService;
    protected $answerService;
    protected $questionnaireSetsRepository;
    protected $questionSetService;
    protected $questionSetsRepository;
    protected $processedDataRepository;
    protected $LDAService;

    public function __construct(
        QuestionService $questionService,
        AnswersRepository $answersRepository,
        InputFieldService $inputFieldService,
        AnswerService $answerService,
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        QuestionSetService $questionSetService,
        QuestionSetsRepository $questionSetsRepository,
        LDAService $LDAService,
        ProcessedDataRepository $processedDataRepository
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
        $this->LDAService = $LDAService;
        $this->processedDataRepository = $processedDataRepository;
    }

    public function summaryWithCategory(Request $request, QuestionSets $set, array $categories)
    {
        $answers = [];
        $options = $request->get("options");
        $nlp = (new NLPService([
            'question_set'  =>  $set,
            'options'       =>  [
                'remove_symbols' => $options["remove_symbols"] ?? null,
                'remove_numbers' => $options["remove_numbers"] ?? null,
                'remove_duplicates' => $options["remove_duplicates"] ?? [],
            ],
            'stop_words'    =>  $request->get("stop_words") ?? null,
            'categories'    =>  $request->get("category") ?? null
        ]));
        foreach ($categories as $category) {
            $answers[$category] = $nlp->getAnswers($category)->clean()->LDA()->getWords();
        }

        return $answers;
    }

    public function reportSummary(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $analysis = (new NLPService([
                'options'       =>  [
                    'remove_symbols' => $options["remove_symbols"] ?? null,
                    'remove_numbers' => $options["remove_numbers"] ?? null,
                    'remove_duplicates' => $options["remove_duplicates"] ?? [],
                ],
                'iterations'    =>  !empty($settings['number_iterations']) ? $settings['number_iterations'] : 50,
                'limit_words'    =>  !empty($settings['number_words']) ? $settings['number_words'] : 5,
                'number_of_topics'    =>  !empty($settings['number_topics']) ? $settings['number_topics'] : 5,
                'stop_words'    =>  $request->get("stop_words") ?? null
            ]))->getReports()->clean()->LDA();

            $this->response->setData(['data' => [
                'thematics_analysis' => $analysis->getWords()
            ]]);
        });
    }

    public function raw($category)
    {
        $concat = '';
        $answers = $this->answersRepository->getCloudAnswers()->toArray();
        foreach ($answers as $answer) {
            if ($category) {
                $category = str_replace('"', "", $category);
                $categories = $this->answersRepository->getCategory($category, $answer['device_address']);
                if ($categories->isEmpty()) {
                    continue;
                }
            }
            $ans = preg_replace('/[\s,.]+/', ' ', $answer['answer']);
            $concat .= $ans. " ";
        }

        return $concat;
    }

    public function rawIncident()
    {
        return $this->runWithExceptionHandling(function () {
            $reportRepo = app()->make(IncidentReportRepository::class);
            $reports = $reportRepo->getUniqueReports();

            $concat = "";
            foreach ($reports as $report) {
                $message = preg_replace('/[\s,.]+/', ' ', $report->message);
                $concat .= " " . $message;
            }

            $this->response->setData(['data' => [
                'topic' => preg_replace('/\s+/', ' ', $concat),
                'name' => "incident_reports.txt"
            ]]);
        });
    }

    public function cleanData(Request $request, $raw = null)
    {
        return $this->runWithExceptionHandling(function () use ($request, $raw) {
            if ($raw) {
                if ($request->has("category") && !empty($request->get("category"))) {
                    $category = $request->get("category");
                    $category = preg_replace("/[^a-zA-Z0-9]/i", "", strtolower($category));
                    $modelName = str_replace('"', "", $category);
                    $topic = $this->raw($request->get("category"));
                    $this->response->setData(['data' => [
                        'topic' => preg_replace('/\s+/', ' ', $topic),
                        'name' => "{$modelName}.txt"
                    ]]);
                }
                return;
            }
            $options = $request->get("options") ? json_decode($request->get("options"), true) : [];
            $settings = $request->get("settings") ? json_decode($request->get("settings"), true) : [];
            $optionData = [
                'options' => [
                    'remove_symbols' => $options["remove_symbols"] ?? null,
                    'remove_numbers' => $options["remove_numbers"] ?? null,
                    'remove_duplicates' => $options["remove_duplicates"] ?? [],
                ],
                'iterations' => !empty($settings['number_iterations']) ? $settings['number_iterations'] : 50,
                'limit_words' => !empty($settings['number_words']) ? $settings['number_words'] : 5,
                'number_of_topics' => !empty($settings['number_topics']) ? $settings['number_topics'] : 5,
                'stop_words' => $request->get("stop_words") ?? null
            ];
            if ($request->get("data_category") === 'Incident Report') {
                $clean = (new NLPService())->getReports()->clean();
                $topic = $clean->getTopic();
            } else {
                $category = $request->get("category") ? json_decode($request->get("category"), true) : [];
                $set = $this->questionSetService->getSet();
                $topic = null;
                foreach ($category as $item) {
                    $clean = (new NLPService(array_merge($optionData, [
                        'question_set' => $set
                    ])))->getAnswers($item)->clean()->topWords();
                    $topic .= $clean->toString() . " ";
                }
            }
            $modelName = $request->get("data_category");
            if ($request->has("category") && !empty($request->get("category")) ||
                $request->has("data_category") && !empty($request->get("data_category"))
            ) {
                $category = $request->has("category") ? $request->get("category") : $request->get("data_category");
                $category = preg_replace("/[^a-zA-Z0-9]/i", "", strtolower($category));
                $modelName = $modelName . "-" . str_replace('"', "", $category);
            }

            $this->response->setData(['data' => [
                'topic' => preg_replace('/\s+/', ' ', $topic),
                'name' => "{$modelName}.txt"
            ]]);
        });
    }

    public function summarize(Request $request, $device = null, $order = null, $setId = null)
    {
        return $this->runWithExceptionHandling(function () use ($request, $order, $setId, $device) {
            $set = $this->questionSetService->getSet();
            $answers = $this->answersRepository->getSetAnswers($set->id, $order);
            $data = [];
            if (isset($answers)) {
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
            }

            if (isset($set)) {
                $percentage = $this->answersRepository->getPercentageAnswers($set->id, $order);
                foreach ($percentage as $percent) {
                    $myAnswer = $this->answersRepository->search([
                        ['device_address', $device],
                        ['questionnaire_id', $percent->questionnaire_id],
                        ['field_id', $percent->field_id],
                    ])->first();
                    $data['percentage'][$percent->questionnaire_id][$percent->input_label] = [
                        'label' => $percent->label,
                        'respondents' => number_format($percent->total),
                        'data' => number_format($percent->answerSum / $percent->total, 2),
                        'answer' => optional($myAnswer)->answer
                    ];
                }
            }
            $published = $this->processedDataRepository->getPublished();
            $clouds = [];
            foreach ($published as $item) {
                $val = $item->data;
                $clouds[] = [
                    'title' => $val['title'],
                    'cloud' => $val['cloud'],
                ];
            }
            $data['cloud'] = $clouds;

            $this->response->setData(['data' => $data]);
        });
    }

    public function summary(Request $request, $device = null, $order = null)
    {
        return $this->runWithExceptionHandling(function () use ($request, $order, $device) {
            $data = [];
            $options = $request->get("options") ? json_decode($request->get("options"), true) : [];
            $settings = $request->get("settings") ? json_decode($request->get("settings"), true) : [];
            $optionData = [
                'options'       =>  [
                    'remove_symbols' => $options["remove_symbols"] ?? null,
                    'remove_numbers' => $options["remove_numbers"] ?? null,
                    'remove_duplicates' => $options["remove_duplicates"] ?? [],
                ],
                'iterations'    =>  !empty($settings['number_iterations']) ? $settings['number_iterations'] : 50,
                'limit_words'    =>  !empty($settings['number_words']) ? $settings['number_words'] : 5,
                'number_of_topics'    =>  !empty($settings['number_topics']) ? $settings['number_topics'] : 5,
                'stop_words'    =>  $request->get("stop_words") ?? null
            ];
            if ($request->has("upload_processed_data_file_path") &&
                $request->get("data_category") === "upload_processed_data") {
                $category = null;
                $topic = file_get_contents($request->get("upload_processed_data_file_path"));
                $ldaTopic = explode("<br/>", wordwrap($topic, 50, "<br/>"));
                $clean = (new NLPService($optionData))->setTopic($topic)->clean();
                $topic = $clean->getTopic();
            } else if ($request->get("data_category") === 'Incident Report') {
                $category = true;
                $clean = (new NLPService($optionData))->getReports()->clean();
                $ldaTopic = $clean->getAllTopic();
                $topic = $clean->getTopic();
            } else {
                $category = $request->get("category");
                $set = $this->questionSetService->getSet();
                $clean = (new NLPService(array_merge($optionData, [
                    'question_set' => $set
                ])))->getAnswers($category)->clean();
                $ldaTopic = $clean->getAllTopic();
                $topic = $clean->getTopic();
            }
            $analysis = $clean->LDA();
            $thematicAnalysis = $analysis->getThematic();
            $cloud = $clean->topWords($category, $topic)->generateCloud()->getWords();

            $modelName = $settings['model_name'] ?? null;
            if ($request->has("category") && !empty($request->get("category")) ||
                $request->has("data_category") && !empty($request->get("data_category"))
            ) {
                $category = $request->has("category") ? $request->get("category") : $request->get("data_category");
                $category = preg_replace("/[^a-zA-Z0-9]/i", "", strtolower($category));
                $modelName = $modelName . "-" . str_replace('"', "", $category);
            }
            $data['title'] = $request->get("title");
            $data['cloud'] = $cloud;
            $data['thematics_analysis'] = $thematicAnalysis;
            $data['model_name'] = $modelName;
            $data['original_model_name'] = $settings['model_name'] ?? null;

            $processed = $this->processedDataRepository->create([
                'data' => $data,
                'processed_by' => Auth::user()->id
            ]);

            $data['processed_id'] = $processed->id;

            $optionData['model_name'] = $modelName;
            $this->LDAService->processLDA($ldaTopic, $optionData);

            $this->response->setData(['data' => $data]);
        });
    }

    public function publish(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $processed_ids = $request->get("processed_ids") ? json_decode($request->get("processed_ids"), true) : [];
            $this->processedDataRepository->publishByIds($processed_ids);

            $this->response->setData(['data' => true]);
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

    public function getLDA(Request $request, $setId = null)
    {
        return $this->runWithExceptionHandling(function () use($request, $setId) {
            $settings = $request->get("settings") ? json_decode($request->get("settings"), true) : [];
            $lda = $this->LDAService->getLDA([
                'stop_words'    =>  $request->post("stop_words"),
                'iterations'    =>  !empty($settings['number_iterations']) ? $settings['number_iterations'] : 50,
                'limit_words'   =>  !empty($settings['number_words']) ? $settings['number_words'] : 5,
                'number_of_topics'  =>  !empty($settings['number_topics']) ? $settings['number_topics'] : 5,
                'model_name'        =>  $request->get("new_model_name") ?:
                    (empty($settings['model_name']) ? $settings['model_name'] : 'defaultModel'),
            ], $setId, $request->get("category"));
            $this->response->setData([
                'data' => $lda
            ]);
        });
    }
}