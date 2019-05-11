<?php

namespace App\Services\Questions;

use Illuminate\Database\Eloquent\Collection;

trait Question
{
    public function generateValidation($validations)
    {
        $validations = json_decode($validations, true);
        $rules = "";
        foreach ($validations as $rule => $value) {
            $rules .= $rule;
            if (!is_bool($value)) {
                $rules .= "{$rules}:{$value}";
            }
            if (count($validations) > 1) {
                $rules .= "|";
            }
        }

        return rtrim($rules, "|");
    }

    public function generateInputs(Collection $inputs)
    {
        $result = [];
        foreach ($inputs as $input) {
            $result[] = [
                'type'  =>  $input->type->name,
                'name'  =>  $input->name,
                'label' =>  $input->label,
                'description'   =>  $input->description,
                'validations'   =>  json_decode($input->validation, true),
                'options'       =>  json_decode($input->options, true)
            ];
        }

        return $result;
    }
}