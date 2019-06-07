<?php

namespace App\Services\Input;

use App\Models\InputFields;
use App\Models\Questions;

abstract class Inputs
{
    protected $inputField;
    protected $type;
    protected $question;
    protected $name;
    protected $label;
    protected $description;
    protected $validations;
    protected $options;
    protected $selectOptions;

    public function setInputField(InputFields $inputField)
    {
        $this->inputField = $inputField;

        return $this;
    }

    public function setQuestion(Questions $question)
    {
        $this->question = $question;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function setValidation(array $validation)
    {
        $this->validations = json_encode($validation);

        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options = json_encode($options);

        return $this;
    }

    public function setSelectOptions(array $selectOptions = [])
    {
        $this->selectOptions = $selectOptions;

        return $this;
    }

    public function create($key, $summary = null)
    {
        $input = $this->createField($this->type, $this->question, [
            'name'          =>  $this->name,
            'label'         =>  $this->label,
            'description'   =>  $this->description,
            'validations'   =>  $this->validations,
            'options'       =>  $this->options,
            'order'         =>  $key,
            'summary'       =>  $summary
        ]);

        if (count($this->selectOptions)) {
            $this->createOptions($this->type, $input, $this->selectOptions);
        }
    }

    public function update()
    {
        $input = $this->updateField($this->inputField, [
            'label'         =>  $this->label,
            'description'   =>  $this->description,
            'validations'   =>  $this->validations,
            'options'       =>  $this->options
        ]);

        if (count($this->selectOptions)) {
            $this->updateOptions($this->inputField->type, $input, $this->selectOptions);
        }
    }
}