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

    protected $thematic;

    protected $topic;

    protected $allTopic = [];

    protected $iterations;

    protected $limitWords;

    protected $stopWords = [];

    protected $cloudWords;

    protected $questionSet;

    protected $top;

    protected $numberOfTopics;

    protected $options = [];

    protected $categories = [];

    public function __construct(array $data = [])
    {
        $this->questionSet = $data['question_set'] ?? null;
        $this->iterations = $data['iterations'] ?? 50;
        $this->limitWords = $data['limit_words'] ?? null;
        $this->numberOfTopics = $data['number_of_topics'] ?? 5;
        $this->top = $data['get_top'] ?? null;
        $this->options = $data['options'] ?? null;
        if (isset($data['stop_words']) && !is_array($data['stop_words'])) {
            $stopWords = trim(preg_replace('/[^a-zA-Z0-9,\s]/i', '', $data['stop_words']));
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
        // Merge stop words from input and default stop words
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

        // train with number of iterations
        $lda->train($tset, $this->iterations);

        $withLimit = $lda->getPhi($this->limitWords);
        $this->words = $lda->getPhi();
        arsort($this->words);
        $words = $this->words[0] ?? $this->words;
        $size = count($words) / $this->numberOfTopics;
        $words = array_chunk($words, $size,true); // true to preserve the keys

        $params = [];
        foreach ($words as $key => $word) {
            $sum = array_sum($word);
            array_splice($word, $this->limitWords);
            if (($key+1)> $this->numberOfTopics) {
                $params[count($params)-1]['params'] += $sum;
                continue;
            }
            $params[] = [
                'params' => $sum,
                'words' => array_keys($withLimit[$key]) ?? null
            ];
        }

        $this->thematic = $params;

        return $this;
    }

    public function getThematic()
    {
        return $this->thematic;
    }

    public function getReports()
    {
        $reportRepo = app()->make(IncidentReportRepository::class);
        $reports = $reportRepo->getUniqueReports();

        $concat = "";
        foreach ($reports as $report) {
            $message = preg_replace('/[\s,.]+/', ' ', $report->message);
            $this->allTopic[] = $message;
            $concat .= " ".$message;
        }

        $this->topic = $concat;

        return $this;
    }

    public function setTopic($topic)
    {
        $this->topic = preg_replace('/[\s,.]+/', ' ', $topic);

        return $this;
    }

    public function getAnswers($category = null)
    {
        if (!$this->questionSet || !($this->questionSet instanceof QuestionSets)) {
            return $this;
        }

        $answersRepo = app()->make(AnswersRepository::class);
        $answers = $answersRepo->getCloudAnswers($this->questionSet->id)->toArray();
        if ($this->options['remove_duplicates']) {
            $temp = array_unique(array_column($answers, 'answer'));
            $answers = array_intersect_key($answers, $temp);
        }
        $concat = "";
        foreach ($answers as $answer) {
            if ($category) {
                $category = str_replace('"', "", $category);
                $categories = $answersRepo->getCategory($category, $answer['device_address']);
                if ($categories->isEmpty()) {
                    continue;
                }
            }
            $ans = preg_replace('/[\s,.]+/', ' ', $answer['answer']);
            $this->allTopic[] = $ans;
            $concat .= $ans. " ";
        }

        $this->topic = $concat;

        return $this;
    }

    public function topWords($category = null, $topic = null)
    {
        $stopWords = array_merge($this->stopWords, config('stop_words'));
        $stopWords = array_map('trim', $stopWords);
        $words = $topic ? explode(" ", $topic) : ($this->words ?: $this->topic);
        if (!$words) {
            return $this;
        }
        if (is_string($words)) {
            $words = explode(" ", $words);
        }
        $words = $this->originalWords = array_diff($words, $stopWords);
        if (!$topic && $category) {
            $words = array_merge(...$words);
            $words = array_map("str_singular", array_keys($words));
        }
        $this->cloudWords = array_count_values($words);
//        $this->cloudWords = $this->numberOfTopics ? array_slice($words, 0, $this->numberOfTopics) : $words;
        $this->topic = $words;

        return $this;
    }

    public function toString($data = null)
    {
        try {
            return implode(" ", $data ?: $this->topic);
        } catch (\Exception $e) {
        }
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
        if (!is_array($this->cloudWords)) {
            return $this;
        }

        arsort($this->cloudWords);
        $data = [];
        $size = 40;
        $c = 0;
        foreach ($this->cloudWords as $word => $count) {
            $data[] = [
                "text"      =>  $word,
                "weight"    =>  $size > 0 ? $size : 1
            ];
            if ($c < 40) {
                if ($c === 1) {
                    $size -= 2.5;
                } else {
                    $size -= 1.3;
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

    public function getTopic()
    {
        return $this->topic;
    }

    public function getAllTopic()
    {
        $stopWords = array_merge($this->stopWords, config('stop_words'));
        $filtered = [];
        foreach ($this->allTopic as $item) {
            if (isset($this->options['remove_symbols'])) {
                $item = preg_replace("/[^a-zA-Z0-9 ]/i", " ", strtolower($item));
            }
            if (isset($this->options['remove_numbers'])) {
                $item = preg_replace("/[^a-zA-Z ]/i", "", strtolower($item));
            }

            $filtered[] = $item;
        }

        $data = [];
        foreach ($filtered as $item) {
            $value = explode(" ", $item);
            $word = "";
            foreach ($value as $val) {
                $word .= !in_array(strtolower($val), $stopWords) ? $val." " : " ";
            }
            $data[] = preg_replace("/ {2,}/", " ", $word);
        };

        return $filtered;
    }

    public function getStopWords()
    {
        return $this->stopWords;
    }
}