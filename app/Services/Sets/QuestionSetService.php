<?php

namespace App\Sets;

use App\Models\LocationBarangays;
use App\Models\User;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Services\Answer\AnswerService;
use App\Utils\Enumerators\QuestionSetStatusEnumerator;
use Illuminate\Support\Facades\Auth;

class QuestionSetService
{
    protected $questionSetsRepository;
    protected $questionnaireSetsRepository;
    protected $questionsRepository;

    public function __construct(
        QuestionSetsRepository $questionSetsRepository,
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        QuestionsRepository $questionsRepository
    ) {
        $this->questionSetsRepository = $questionSetsRepository;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
        $this->questionsRepository = $questionsRepository;
    }

    public function create(User $user, array $data)
    {
        $set = $this->questionSetsRepository->create([
            'created_by'    =>  $user->id,
            'title'         =>  $data['title'],
            'description'   =>  $data['description'] ?? null
        ]);

        if (count($data['question_ids'])) {
            foreach ($data['question_ids'] as $order => $question_id) {
                $question = $this->questionsRepository->getById($question_id);
                if ($question) {
                    $this->questionnaireSetsRepository->create([
                        'set_id' => $set->id,
                        'question_id' => $question->id,
                        'order' => ++$order
                    ]);
                }
            }
        }

        return $set;
    }

    public function getDefault()
    {
        return $this->questionSetsRepository->find([
            ['location_id', null],
            ['status', QuestionSetStatusEnumerator::DEFAULT]
        ]);
    }

    public function getSet(LocationBarangays $locationBarangay = null)
    {
        if ($locationBarangay) {
            $set = $this->questionSetsRepository->find([
                ['location_id', $locationBarangay->id],
                ['status', QuestionSetStatusEnumerator::DEFAULT]
            ]);
        } else {
            $set = $this->getDefault();
        }

        return $set;
    }
}