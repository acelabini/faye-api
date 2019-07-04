<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InputField
 * @package App\Models
 * @property integer input_field_id
 * @property string label
 * @property string value
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class InputFieldOptions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'input_field_id',
        'label',
        'value'
    ];

    public function field()
    {
        return $this->belongsTo(InputFields::class, 'input_field_id');
    }
}
