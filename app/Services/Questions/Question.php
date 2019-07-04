<?php

namespace App\Services\Questions;

use Illuminate\Database\Eloquent\Collection;

trait Question
{
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