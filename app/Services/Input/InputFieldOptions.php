<?php

namespace App\Services\Input;

use App\Models\InputFields;
use App\Models\InputFieldType;
use Illuminate\Support\Facades\Log;

trait InputFieldOptions
{
    public function createOptions(InputFieldType $inputFieldType, InputFields $inputField, array $data) : bool
    {
        if ($inputFieldType->name !== self::SELECT_TYPE) {
            return false;
        }

        foreach ($this->selectOptions as $value => $label) {
            $this->inputFieldOptionsRepository->create([
                'input_field_id'    =>  $inputField->id,
                'label'             =>  $label,
                'value'             =>  $value
            ]);
        }

        return true;
    }
}