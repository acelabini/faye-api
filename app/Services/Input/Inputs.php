<?php

namespace App\Services\Input;

abstract class Inputs
{
    protected $type;
    protected $question;
    protected $name;
    protected $label;
    protected $description;
    protected $validations;
    protected $options;
    protected $fieldOptions;

    public function setQuestion($question)
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
        $this->description = json_encode($description);

        return $this;
    }

    public function setValidation($validation)
    {
        $this->validations = json_encode($validation);

        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options = json_encode($options);

        return $this;
    }

    public function setFieldOptions(array $fieldOptions = [])
    {
        $this->fieldOptions = $fieldOptions;

        return $this;
    }

    public function create()
    {
        $input = $this->createField($this->type, $this->question, [
            'name'          =>  $this->name,
            'label'         =>  $this->label,
            'description'   =>  $this->description,
            'validations'   =>  $this->validations,
            'options'       =>  $this->options
        ]);

        if (count($this->fieldOptions)) {
            $this->createOptions($this->type, $input, [
                'label' => $this->fieldOptions['label'],
                'value' => $this->fieldOptions['value']
            ]);
        }
    }
}