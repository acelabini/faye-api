<?php

namespace App\Services;

use App\Models\QuestionSets;
use App\Repositories\AnswersRepository;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;
use NlpTools\Utils\StopWords;

class NLPService
{
    protected $words;

    protected $topic;

    protected $train;

    protected $limitWords;

    protected $questionSet;

    protected $top;

    public function __construct(array $data = [])
    {
        $this->questionSet = $data['question_set'] ?? null;
        $this->train = $data['train'] ?? 50;
        $this->limitWords = $data['limitWords'] ?? 10;
        $this->top = $data['get_top'] ?? null;
    }

    public function LDA()
    {
        if (!$this->topic) {
            return $this;
        }

        $topic = preg_replace("/[^a-zA-Z ]/i", "", strtolower($this->topic));
        $tok = new WhitespaceTokenizer();
        $tset = new TrainingSet();
        $stopWords = config('stop_words');
        $stop = new StopWords($stopWords);
        $d = new TokensDocument(explode(" ", $topic));
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
            5, // the number of topics we want
            1, // the dirichlet prior assumed for the per document topic distribution
            1  // the dirichlet prior assumed for the per word topic distribution
        );

        $lda->train($tset, $this->train);

        $this->words = $lda->getWordsPerTopicsProbabilities($this->limitWords);

        return $this;
    }

    public function getAnswers()
    {
        if (!$this->questionSet || !($this->questionSet instanceof QuestionSets)) {
            return $this;
        }

        $answersRepo = app()->make(AnswersRepository::class);
        $answers = $answersRepo->getCloudAnswers($this->questionSet->id)->toArray();

        $concat = "";
        foreach ($answers as $answer) {
            $concat .= $answer['answer'];
        }

        $this->topic = $concat;

        return $this;
    }

    public function topWords()
    {
        if (!$this->words || !is_array($this->words)) {
            return $this;
        }

        $words = array_merge(...$this->words);
        arsort($words);
        $words = array_unique(array_map("str_singular", array_keys($words)));

        $this->words = $this->top ? array_slice($words, 0, $this->top) : $words;

        return $this;
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