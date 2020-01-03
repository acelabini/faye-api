<?php

namespace App\Services;

use App\Models\QuestionSets;
use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use Illuminate\Support\Facades\Log;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;
use NlpTools\Utils\StopWords;

class NLPService
{
    protected $words;

    protected $originalWords;

    protected $topic;

    protected $iterations;

    protected $limitWords;

    protected $stopWords = [];

    protected $questionSet;

    protected $top;

    protected $numberOfTopics;

    protected $options = [];

    protected $categories = [];

    public function __construct(array $data = [])
    {
        $this->questionSet = $data['question_set'] ?? null;
        $this->iterations = $data['iterations'] ?? 50;
        $this->limitWords = $data['limit_words'] ?? 5;
        $this->numberOfTopics = $data['number_of_topics'] ?? 5;
        $this->top = $data['get_top'] ?? null;
        $this->options = $data['options'] ?? null;
        if (isset($data['stop_words'])) {
            $stopWords = trim(preg_replace('/\s+/', '', $data['stop_words']));
            $this->stopWords = explode(",", $stopWords);
        }
        $this->categories = !empty($data['categories']) ? json_decode($data['categories']) : [];
    }

    public function clean()
    {
        if (!$this->topic) {
            return $this;
        }
        if (isset($this->options['remove_symbols'])) {
            $this->topic = preg_replace("/[^a-zA-Z0-9 ]/i", "", strtolower($this->topic));
        }
        if (isset($this->options['remove_numbers'])) {
            $this->topic = preg_replace("/[^a-zA-Z ]/i", "", strtolower($this->topic));
        }

        return $this;
    }

    public function LDA($topic = null)
    {
        if (!$this->topic) {
            return $this;
        }

        $tok = new WhitespaceTokenizer();
        $tset = new TrainingSet();
        $stopWords = array_merge($this->stopWords, config('stop_words'));
        $stop = new StopWords($stopWords);
        $d = new TokensDocument(explode(" ", $topic ?: $this->topic));
        $d->applyTransformation($stop);

        $tset->addDocument(
            '', // the class is not used by the lda model
            new TokensDocument(
                $tok->tokenize(
                    implode(" ", $d->getDocumentData())
                )
            )
        );

        $lda = new Lda(
            new DataAsFeatures(), // a feature factory to transform the document data
            $this->numberOfTopics, // the number of topics we want
            1, // the dirichlet prior assumed for the per document topic distribution
            1  // the dirichlet prior assumed for the per word topic distribution
        );

        $lda->train($tset, $this->iterations);

        $this->words = $lda->getPhi($this->limitWords);

        return $this;
    }

    public function getReports()
    {
        $reportRepo = app()->make(IncidentReportRepository::class);
        $reports = $reportRepo->search([
            ['status', 'confirmed']
        ]);

        $concat = "";
        foreach ($reports as $report) {
            $concat .= " ".$report->message;
        }

        $this->topic = $concat;

        return $this;
    }

    public function getAnswers($category = null)
    {
        if (!$this->questionSet || !($this->questionSet instanceof QuestionSets)) {
            return $this;
        }

        $answersRepo = app()->make(AnswersRepository::class);
        $answers = $answersRepo->getCloudAnswers($this->questionSet->id)->toArray();

        $concat = "";
        foreach ($answers as $answer) {
            if ($category) {
                $category = str_replace('"', "", $category);
                $categories = $answersRepo->getCategory($category, $answer['device_address']);
                if ($categories->isEmpty()) {
                    continue;
                }
            }
            $concat .= $answer['answer'];
        }

        $this->topic = $concat;

        return $this;
    }

    public function topWords($category = null)
    {
        if (!$this->words || !is_array($this->words)) {
            return $this;
        }
        $this->originalWords = $this->words;

        $words = array_merge(...$this->words);
        arsort($words);
        $words = array_unique(array_map("str_singular", array_keys($words)));

        $this->words = $this->numberOfTopics ? array_slice($words, 0, $this->numberOfTopics) : $words;
        if ($category) {
            $this->words = $words;
        }

        return $this;
    }

    public function sortOriginal()
    {
        if (!$this->originalWords || !is_array($this->originalWords)) {
            return $this;
        }

        $words = array_merge(...$this->originalWords);
        $words = array_unique(array_map("str_singular", array_keys($words)));

        $this->originalWords = $this->numberOfTopics ? array_slice($words, 0, $this->numberOfTopics) : $words;

        return $this;
    }

    public function getOriginal()
    {
        return $this->originalWords;
    }

    public function generateCloud()
    {
        if (!is_array($this->words)) {
            return $this;
        }

        $data = [];
        $size = 40;
        $c = 0;
        foreach ($this->words as $key => $word) {
            $data[] = [
                "text"      =>  $word,
                "weight"    =>  $size > 0 ? $size : 1
            ];
            if ($c < 5) {
                $size -= 2.5;
                if ($c === 1) {
                    $size -= 3.5;
                }
                $c++;
            } else {
                $size--;
            }
        }

        $this->words = $data;

        return $this;
    }

    public function getWords()
    {
        return $this->words;
    }
}