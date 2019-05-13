<?php

namespace App\Services\Questions;

use App\Models\User;
use App\Repositories\InputFieldTypeRepository;
use App\Repositories\QuestionsRepository;
use App\Services\Input\InputFieldService;

class QuestionService
{
    use Question;

    protected $questionsRepository;
    protected $inputFieldService;
    protected $inputFieldTypeRepository;

    public function __construct(
        QuestionsRepository $questionsRepository,
        InputFieldTypeRepository $inputFieldTypeRepository,
        InputFieldService $inputFieldService
    ) {
        $this->questionsRepository = $questionsRepository;
        $this->inputFieldTypeRepository = $inputFieldTypeRepository;
        $this->inputFieldService = $inputFieldService;
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
}