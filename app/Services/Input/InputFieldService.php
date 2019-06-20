<?php

namespace App\Services\Input;

use App\Models\InputFields;
use App\Models\InputFieldType;
use App\Models\Questions;
use App\Repositories\InputFieldOptionsRepository;
use App\Repositories\InputFieldsRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class InputFieldService extends Inputs
{
    use InputField;

    const VALID_TYPES = [
        'select',
        'radio',
        'checkbox'
    ];

    protected $inputFieldOptionsRepository;
    protected $inputFieldsRepository;

    public function __construct(
        InputFieldsRepository $inputFieldsRepository,
        InputFieldOptionsRepository $inputFieldOptionsRepository
    ) {
        $this->inputFieldsRepository = $inputFieldsRepository;
        $this->inputFieldOptionsRepository = $inputFieldOptionsRepository;
    }

    public function getById($id)
    {
        return $this->inputFieldsRepository->getById($id);
    }

    protected function createField(InputFieldType $inputFieldType, Questions $question, array $data)
    {
        return $this->inputFieldsRepository->create([
            'type_id'       =>  $inputFieldType->id,
            'question_id'   =>  $question->id,
            'order'         =>  $data['order'],
            'name'          =>  $data['name'],
            'label'         =>  $data['label'],
            'description'   =>  $data['description'] ?? null,
            'validations'   =>  $data['validations'] ?? null,
            'options'       =>  $data['options'] ?? null,
            'summary'       =>  $data['summary'] ?? null
        ]);
    }

    protected function updateField(InputFields $inputField, array $data)
    {
        return $this->inputFieldsRepository->update($inputField, [
            'label'         =>  $data['label'],
            'order'         =>  $data['order'] ?: $inputField->order,
            'description'   =>  $data['description'] ?? null,
            'validations'   =>  $data['validations'] ?? null,
            'options'       =>  $data['options'] ?? null,
            'summary'       =>  $data['summary'] ?? null
        ]);
    }

    public function getInputs(Questions $question)
    {
        return $this->inputFieldsRepository->getInputs($question->inputs()->pluck('id')->toArray());
    }

    public function validateInputFields(Collection $inputFields) : array
    {
        $validations = [];
        foreach ($inputFields as $inputField) {
            $validations["{$inputField->name}.*.answer"] = $this->generateValidation($inputField->validations);
        }
        return $validations;
    }
}