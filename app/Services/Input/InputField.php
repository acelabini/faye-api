<?php

namespace App\Services\Input;

use App\Models\InputFields;
use App\Models\InputFieldType;
use Illuminate\Support\Facades\Log;

trait InputField
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

    public function updateOptions(InputFieldType $inputFieldType, InputFields $inputField, array $data) : bool
    {
        if ($inputFieldType->name !== self::SELECT_TYPE) {
            return false;
        }

        foreach ($this->selectOptions as $id => $label) {
            $option = $this->inputFieldOptionsRepository->search([
                ['id', $id]
            ]);
            if (!$option) continue;

            $this->inputFieldOptionsRepository->update($option, [
                'label'             =>  $label,
                'value'             =>  $label
            ]);
        }

        return true;
    }

    /**
     * Generate a validation format
     * {"min":1,"max":10,"required":true} to min:1|max:10|required
     * @param $validations
     * @return string
     */
    public function generateValidation($validations)
    {
        $validations = json_decode($validations, true);
        $rules = "";
        foreach ($validations as $rule => $value) {
            $rules .= $rule;
            if (!is_bool($value)) {
                $rules .= ":{$value}";
            }
            if (count($validations) > 1) {
                $rules .= "|";
            }
        }

        return rtrim($rules, "|");
    }
}