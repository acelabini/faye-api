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

    public function create(User $user, array $data, LocationBarangays $locationBarangays = null)
    {
        $set = $this->questionSetsRepository->create([
            'created_by'    =>  $user->id,
            'title'         =>  $data['title'],
            'description'   =>  $data['description'] ?? null,
            'status'        =>  $data['default']
                ? QuestionSetStatusEnumerator::DEFAULT : QuestionSetStatusEnumerator::ACTIVE
        ]);

        if ($locationBarangays) {

        }

        if ($data['default'] === QuestionSetStatusEnumerator::DEFAULT) {
            $default = $this->questionSetsRepository->search([
                'location_id'   =>  optional($locationBarangays)->id ?? null,
                'status'        =>  QuestionSetStatusEnumerator::DEFAULT
            ]);

            if ($default->isNotEmpty()) {
               $this->questionSetsRepository->update($default->first(), [
                   'status'        =>  QuestionSetStatusEnumerator::ACTIVE
               ]) ;
            }
        }

        if (count($question_ids = $data['question_ids'])) {
            $question_ids = array_values($question_ids);
            foreach ($question_ids as $order => $question_id) {
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
        if ($locationBarangay && !env('SKIP_QUESTIONS')) {
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