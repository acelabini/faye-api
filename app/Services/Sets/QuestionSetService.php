<?php

namespace App\Sets;

use App\Models\LocationBarangays;
use App\Models\QuestionSets;
use App\Models\User;
use App\Repositories\AnswersRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Services\Answer\AnswerService;
use App\Utils\Enumerators\QuestionSetStatusEnumerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $isDefault = filter_var($data['default'], FILTER_VALIDATE_BOOLEAN);
        if ($isDefault) {
            $default = $this->questionSetsRepository->search([
                ['location_id', optional($locationBarangays)->id],
                ['status', QuestionSetStatusEnumerator::DEFAULT]
            ]);

            if ($default->isNotEmpty()) {
                $this->questionSetsRepository->update($default->first(), [
                    'status'        =>  QuestionSetStatusEnumerator::ACTIVE
                ]) ;
            }
        }

        $set = $this->questionSetsRepository->create([
            'created_by'    =>  $user->id,
            'title'         =>  $data['title'],
            'description'   =>  $data['description'] ?? null,
            'status'        =>  $isDefault
                ? QuestionSetStatusEnumerator::DEFAULT : QuestionSetStatusEnumerator::ACTIVE
        ]);

        if ($locationBarangays) {

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

    public function patch(QuestionSets $set, array $data, LocationBarangays $locationBarangays = null)
    {
        $isDefault = filter_var($data['default'], FILTER_VALIDATE_BOOLEAN);
        if ($isDefault) {
            $default = $this->questionSetsRepository->search([
                ['location_id', optional($locationBarangays)->id],
                ['status', QuestionSetStatusEnumerator::DEFAULT]
            ]);

            if ($default->isNotEmpty()) {
                $this->questionSetsRepository->update($default->first(), [
                    'status'        =>  QuestionSetStatusEnumerator::ACTIVE
                ]) ;
            }
        }

        $set = $this->questionSetsRepository->update($set, [
            'title'         =>  $data['title'],
            'description'   =>  $data['description'] ?? $set->description,
            'status'        =>  $isDefault
                ? QuestionSetStatusEnumerator::DEFAULT : QuestionSetStatusEnumerator::ACTIVE
        ]);

        if ($locationBarangays) {
            //
        }

        if (count($question_ids = $data['question_ids'])) {
            $question_ids = array_values($question_ids);
            foreach ($question_ids as $order => $question_id) {
                $question = $this->questionsRepository->getById($question_id);
                if ($question) {
                    $exist = $this->questionnaireSetsRepository->search([
                        ['set_id', $set->id],
                        ['question_id', $question->id]
                    ]);
                    if ($exist->isNotEmpty()) {
                        $this->questionnaireSetsRepository->update($exist->first(), [
                            'order' =>  ++$order
                        ]);
                    } else {
                        $this->questionnaireSetsRepository->create([
                            'set_id' => $set->id,
                            'question_id' => $question->id,
                            'order' => ++$order
                        ]);
                    }
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