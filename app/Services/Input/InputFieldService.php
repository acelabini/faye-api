<?php

namespace App\Services\Input;

use App\Models\InputFieldType;
use App\Models\Questions;
use App\Repositories\InputFieldOptionsRepository;
use App\Repositories\InputFieldsRepository;

class InputFieldService extends Inputs
{
    use InputFieldOptions;

    const SELECT_TYPE = 'select';

    protected $inputFieldOptionsRepository;
    protected $inputFieldsRepository;

    public function __construct(
        InputFieldsRepository $inputFieldsRepository,
        InputFieldOptionsRepository $inputFieldOptionsRepository
    ) {
        $this->inputFieldsRepository = $inputFieldsRepository;
        $this->inputFieldOptionsRepository = $inputFieldOptionsRepository;
    }

    protected function createField(InputFieldType $inputFieldType, Questions $question, array $data)
    {
        return $this->inputFieldsRepository->create([
            'type_id'       =>  $inputFieldType->id,
            'question_id'   =>  $question->id,
            'name'          =>  $data['name'],
            'label'         =>  $data['label'],
            'description'   =>  $data['description'] ?? null,
            'validations'   =>  $data['validations'] ?? null,
            'options'       =>  $data['options'] ?? null
        ]);
    }
}