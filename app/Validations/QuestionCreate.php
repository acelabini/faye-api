<?php

namespace App\Validations;

class QuestionCreate extends Validation
{
    public static function getRules()
    {
        return [
            'title'     =>  'required',
            'inputs'    =>  'required',
            'inputs.*.type'  =>  'required|exists:input_field_type,name',
            'inputs.*.name'  =>  'required',
            'inputs.*.label'  =>  'required',
        ];
    }

    public static function getMessages()
    {
        return [
            'inputs.*.type.required'  =>  'Input type is required.',
            'inputs.*.type.exists'  =>  'Input type is invalid.',
            'inputs.*.name.required'     =>  'Input name is required.',
            'inputs.*.label.required'    =>  'Input label is required.',
        ];
    }
}