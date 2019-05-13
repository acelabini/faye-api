<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InputFields
 * @package App\Models
 * @property integer type_id
 * @property integer question_id
 * @property string name
 * @property string label
 * @property string description
 * @property string validations
 * @property string options
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class InputFields extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type_id',
        'question_id',
        'name',
        'label',
        'description',
        'validations',
        'options'
    ];

    public function type()
    {
        return $this->belongsTo(InputFieldType::class, 'type_id');
    }

    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }

    public function selectOptions()
    {
        return $this->hasMany(InputFieldOptions::class, 'input_field_id');
    }

    public function getGenerateOptionsAttribute()
    {
        $selectOptions = [];
        foreach ($this->selectOptions as $option) {
            $selectOptions[] = [
                'value' =>  $option->value,
                'label' =>  $option->label
            ];
        }

        return $selectOptions;
    }
}
