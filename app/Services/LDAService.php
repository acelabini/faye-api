<?php

namespace App\Services;

use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
            'path'          =>  'data',
            'num_topics'    =>  $options['number_of_topics'] ?? 10,
            'stop_words'    =>  $options['stop_words'] ?? null,
            'iterations'    =>  $options['iterations'] ?? 50,
            'limit_words'   =>  $options['limit_words'] ?? 5,
            'answers'       =>  $postAnswers,
        ];

        return $this->lda($data);
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

        $url = 'http://lda.jen.test/createLDA.php';
        $client = new Client();

        $response = $client->post($url, [
            'form_params'   =>  [
                'data'  =>  $data
            ]
        ]);

        return $response->getBody()->getContents();
    }

    public function lda(array $data)
    {
        $answers = $data['answers'];
        $stopWords = $data['stop_words'] ?? null;
        $modelName = $data['model_name'] ?? null;
        $rPath = $data['path'] ?? null;
        $numOfTopics = $data['num_topics'] ?? null;
        $modelName = $this->clean(strtolower($modelName));
        $basicPath = public_path("lda/{$rPath}/{$modelName}");
        $dir = "{$basicPath}/data";

        if (!@mkdir($dir)) {
            $error = error_get_last();
            echo $error['message'] ?? $error."\n";
            Log::critical($error['message'] ?? $error);
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        } else {
            system("rm -rf {$basicPath}");
        }

        if (is_array($answers)) {
            foreach ($answers as $answer) {
                if ($stopWords) {
                    foreach ($stopWords as $stopWord) {
                        $answer =  preg_replace("/\b{$stopWord}\b/", "", $answer);
                    }
                }
                $id = uniqid();
                $file = fopen("{$dir}/$id", "wb");
                fwrite($file, $answer);
                fclose($file);
            }
            $py = base_path()."/lda.py";
            $rPath = public_path("lda/{$rPath}");
            exec("/usr/bin/python3.8 {$py} {$rPath} {$modelName} {$numOfTopics}", $output, $result);
            return $output;
        }

        return null;
    }

    function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}
