<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Questions
 * @package App\Models
 * @property integer created_by
 * @property string title
 * @property string description
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class Questions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inputs()
    {
        return $this->hasMany(InputFields::class, 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answers::class, 'question_id');
    }

    public function getGenerateInputsAttribute()
    {
        $inputs = [];
        foreach ($this->inputs as $input) {
            $inputs[] = [
                'type'  =>  $input->type->name,
                'name'  =>  $input->name,
                'label' =>  $input->label,
                'description'   =>  $input->description,
                'validations'   =>  json_decode($input->validations, true),
                'options'       =>  json_decode($input->options, true),
                'select_options'    =>  $input->generate_options
            ];
        }

        return $inputs;
    }
}
