<?php

namespace App\Services\Questions;

use App\Models\LocationBarangays;
use App\Models\Questions;
use App\Models\QuestionSets;
use App\Models\User;
use App\Repositories\AnswersRepository;
use App\Repositories\InputFieldTypeRepository;
use App\Repositories\QuestionnaireSetsRepository;
use App\Repositories\QuestionsRepository;
use App\Services\Answer\AnswerService;
use App\Services\Input\InputFieldService;
use App\Sets\QuestionSetService;

class QuestionService
{
    use Question;

    protected $questionsRepository;
    protected $questionnaireSetsRepository;
    protected $inputFieldService;
    protected $inputFieldTypeRepository;
    protected $answersRepository;
    protected $questionSetService;

    public function __construct(
        QuestionsRepository $questionsRepository,
        InputFieldTypeRepository $inputFieldTypeRepository,
        QuestionnaireSetsRepository $questionnaireSetsRepository,
        InputFieldService $inputFieldService,
        AnswersRepository $answersRepository,
        QuestionSetService $questionSetService
    ) {
        $this->questionsRepository = $questionsRepository;
        $this->questionnaireSetsRepository = $questionnaireSetsRepository;
        $this->inputFieldTypeRepository = $inputFieldTypeRepository;
        $this->inputFieldService = $inputFieldService;
        $this->answersRepository = $answersRepository;
        $this->questionSetService = $questionSetService;
    }

    public function create(User $user, array $data)
    {
        $question = $this->questionsRepository->create([
            'created_by'   =>   $user->id,
            'title'        =>   $data['title'],
            'description'  =>   $data['description']
        ]);
        $field = $this->inputFieldService->setQuestion($question);

        foreach ($data['inputs'] as $input) {
            $type = $this->inputFieldTypeRepository->get($input['type_id']);
            $field->setType($type)
                ->setName($input['name'])
                ->setLabel($input['label'])
                ->setDescription($input['description'] ?? null)
                ->setValidation($input['validations'] ?? [])
                ->setOptions($input['options'] ?? [])
                ->setSelectOptions($input['select_options'] ?? [])
                ->create();
        }

        return $question;
    }

    public function generateQuestion(int $questionId) : array
    {
        $question = $this->questionsRepository->get($questionId);

        return [
            'title'         =>  $question->title,
            'description'   =>  $question->description,
            'inputs'        =>  $this->generateInputs($question->inputs)
        ];
    }

    // A = if post have location, get the question with the location id
    // get the last question answered
    // B = get the set of the question
    // check if set A is the same with set B
    // if same, get the next question
    // else get the new set of questions
    // if null meaning the set of question is done
    public function getCurrentQuestion(LocationBarangays $locationBarangay = null, int $order = null)
    {
        $set = $this->questionSetService->getSet($locationBarangay);
        $identifier = AnswerService::getAnswerIdentifier();
        $lastAnswered = $this->answersRepository->search([
            ['user_id', $identifier['user_id']],
            ['device_address', $identifier['device_address']]
        ]);

        $currentSet = $set->questionnaires->first();
        if (count($lastAnswered)) {
            $lastAnswered = $lastAnswered->last();
            if ($lastAnswered->questionnaire->set_id === $set->id) {
                $currentOrder = $lastAnswered->questionnaire->order;
                $currentSet = $this->questionnaireSetsRepository->getQuestionBySetAndOrder(
                    $set,
                    $currentOrder + 1
                );
                if ($order && $order <= $currentOrder) {
                    $currentSet = $this->questionnaireSetsRepository->getQuestionBySetAndOrder(
                        $set,
                        $order
                    );
                }
            }
        }

        return [optional($currentSet)->question, $currentSet];
    }
}