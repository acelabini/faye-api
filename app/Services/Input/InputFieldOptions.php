<?php

namespace App\Services\Input;

use App\Models\InputFields;
use App\Models\InputFieldType;

trait InputFieldOptions
{
    public function createOptions(InputFieldType $inputFieldType, InputFields $inputField, array $data) : bool
    {
        if ($inputFieldType->type !== self::SELECT_TYPE) {
            return false;
        }

        foreach ($data as $datum) {
            $this->inputFieldOptionsRepository->create([
                'input_field_id'    =>  $inputField->id,
                'label'             =>  $datum['label'],
                'value'             =>  $datum['value']
            ]);
        }

        return true;
    }
}