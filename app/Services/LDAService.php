<?php

namespace App\Services;

use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class LDAService
{
    public $answersRepository;
    public $nlpService;

    public function __construct(
        AnswersRepository $answersRepository,
        NLPService $nlpService
    ) {
        $this->answersRepository = $answersRepository;
        $this->nlpService = $nlpService;
    }

    public function processLDA(array $postAnswers, array $options)
    {
        $data = [
            'model_name'    =>  $options['model_name'] ?? null,
            'path'          =>  'jenLDA',
            'num_topics'    =>  $options['number_of_topics'] ?? 10,
            'stop_words'    =>  $options['stop_words'] ?? null,
            'iterations'    =>  $options['iterations'] ?? 50,
            'limit_words'   =>  $options['limit_words'] ?? 5,
            'answers'       =>  $postAnswers,
        ];

        $url = 'http://159.89.193.192/lda/createLDA.php';
        $client = new Client();

        $response = $client->post($url, [
            'form_params'   =>  [
                'data'  =>  $data
            ]
        ]);

        return $response->getBody()->getContents();
    }

    public function getLDA(array $data, $setId = null, $category = null)
    {
        $postAnswers = [];
        if ($setId ) {
            $answers = $this->answersRepository->getCloudAnswers($setId)->toArray();
            foreach ($answers as $answer) {
                if ($category) {
                    $category = str_replace('"', "", $category);
                    $categories = $this->answersRepository->getCategory($category, $answer['device_address']);
                    if ($categories->isEmpty()) {
                        continue;
                    }
                }
                $postAnswers[] = $answer['answer'];
            }
        } else {
            $postAnswers = $this->nlpService->getReports()->getAllTopic();
        }

        $data = [
            'model_name'    =>  $data['model_name'] ?? null,
            'path'          =>  'jenLDA',
            'num_topics'    =>  $data['number_of_topics'] ?? 10,
            'stop_words'    =>  $data['stop_words'] ?? null,
            'iterations'    =>  $data['iterations'] ?? 50,
            'limit_words'   =>  $data['limit_words'] ?? 5,
            'answers'       =>  $postAnswers,
        ];

        $url = 'http://159.89.193.192/lda/createLDA.php';
        $client = new Client();

        $response = $client->post($url, [
            'form_params'   =>  [
                'data'  =>  $data
            ]
        ]);

        return $response->getBody()->getContents();
    }
}
