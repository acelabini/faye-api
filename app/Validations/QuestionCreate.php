<?php

namespace App\Validations;

class QuestionCreate extends Validation
{
    public static function getRules()
    {
        return [
            'title'     =>  'required',
            'inputs'    =>  'required',
            'inputs.*.type_id'  =>  'required',
            'inputs.*.name'  =>  'required',
            'inputs.*.label'  =>  'required',
        ];
    }

    public static function getMessages()
    {
        return [
            'inputs.*.type_id.required'  =>  'Input type is required.',
            'inputs.*.name.required'     =>  'Input name is required.',
            'inputs.*.label.required'    =>  'Input label is required.',
        ];
    }
}