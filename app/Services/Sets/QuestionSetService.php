<?php

namespace App\Sets;

use App\Models\User;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionSetsRepository;
use App\Repositories\QuestionsRepository;

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
}