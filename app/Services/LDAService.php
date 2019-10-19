<?php

namespace App\Services;

use App\Repositories\AnswersRepository;

class LDAService
{
    public $answersRepository;

    public function __construct(AnswersRepository $answersRepository)
    {
        $this->answersRepository = $answersRepository;
    }

    public function getLDA($setId)
    {
        $answers = $this->answersRepository->getCloudAnswers($setId)->toArray();
        $data = [
            'answers'       =>  array_column($answers, "answer"),
            'model_name'    =>  'test',
            'path'          =>  'jenLDA',
            'num_topics'    =>  10
        ];

        $url = 'http://159.89.193.192/lda/createLDA.php';
        // build the urlencoded data
        $postData = http_build_query(['data' => $data]);

        // open connection
        $ch = curl_init();

        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // execute post
        $result = curl_exec($ch);

        // close connection
        curl_close($ch);

        return $result;
    }
}