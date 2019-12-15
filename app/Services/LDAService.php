<?php

namespace App\Services;

use App\Repositories\AnswersRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class LDAService
{
    public $answersRepository;

    public function __construct(AnswersRepository $answersRepository)
    {
        $this->answersRepository = $answersRepository;
    }

    public function getLDA(array $data, $setId)
    {
        $answers = $this->answersRepository->getCloudAnswers($setId)->toArray();
        $data = [
            'answers'       =>  array_column($answers, "answer"),
            'model_name'    =>  $data['model_name'] ?? null,
            'path'          =>  'jenLDA',
            'num_topics'    =>  $data['number_of_topics'] ?? 10,
            'stop_words'    =>  $data['stop_words'] ?? null,
            'iterations'    =>  $data['iterations'] ?? 50,
            'limit_words'   =>  $data['limit_words'] ?? 5
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