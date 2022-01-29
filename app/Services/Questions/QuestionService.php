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
use App\Services\Answers\AnswerService;
use App\Services\Input\InputFieldService;
use App\Services\Sets\QuestionSetService;
use Illuminate\Support\Facades\Log;

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

        foreach ($data['inputs'] as $key => $input) {
            $type = $this->inputFieldTypeRepository->search([
                ['name', $input['type']]
            ]);
            if (!count($type)) continue;

            $field->setType($type->first())
                ->setName($input['name'])
                ->setLabel($input['label'])
                ->setDescription($input['description'] ?? null)
                ->setValidation($input['validations'] ?? [])
                ->setOptions($input['options'] ?? [])
                ->setSelectOptions($input['select_options'] ?? [])
                ->create(++$key, $input['summary'] ?? null);
        }

        return $question;
    }

    public function update(Questions $question, array $data)
    {
        $removeInputs = array_diff(
                $question->inputs->pluck('id')->toArray(),
                array_filter(array_column($data['inputs'], 'field_id'))
        );
        $this->inputFieldService->removeInputs($removeInputs);
        $question = $this->questionsRepository->update($question, [
            'title'        =>   $data['title'],
            'description'   =>  $data['description'] ?: $question->description
        ]);
        $field = $this->inputFieldService->setQuestion($question);

        foreach ($data['inputs'] as $key => $input) {
            if (isset($input['field_id'])) {
                $inputField = $this->inputFieldService->getById($input['field_id']);
                if (!$inputField) continue;

                $field->setInputField($inputField)
                    ->setLabel($input['label'])
                    ->setDescription($input['description'] ?? null)
                    ->setValidation($input['validations'] ?? [])
                    ->setOptions($input['options'] ?? [])
                    ->setSelectOptions($input['select_options'] ?? [])
                    ->update(++$key, $input['summary'] ?? null);
            } else {
                $type = $this->inputFieldTypeRepository->search([
                    ['name', $input['type']]
                ]);
                if (!count($type)) continue;

                $field->setType($type->first())
                    ->setName($input['name'])
                    ->setLabel($input['label'])
                    ->setDescription($input['description'] ?? null)
                    ->setValidation($input['validations'] ?? [])
                    ->setOptions($input['options'] ?? [])
                    ->setSelectOptions($input['select_options'] ?? [])
                    ->create(++$key, $input['summary'] ?? null);
            }
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
    public function getCurrentQuestion(LocationBarangays $locationBarangay = null, int $order = null, $setId = null)
    {
        $set = $this->questionSetService->getSet($locationBarangay, $setId);
        $identifier = AnswerService::getAnswerIdentifier(request()->get('device'));
        $lastAnswered = $this->answersRepository->search([
            ['user_id', $identifier['user_id']],
            ['device_address', $identifier['device_address']]
        ]);

        $currentSet = $set->questionnaires->first();
        if (count($lastAnswered)&& !env('SKIP_QUESTIONS')) {
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
        } else if (env('SKIP_QUESTIONS')) {
            $currentSet = $this->questionnaireSetsRepository->getQuestionBySetAndOrder(
                $set,
                $order
            );
        }
        return [optional($currentSet)->question, $currentSet];
    }
}

