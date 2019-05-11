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
 * @property string input_field_id
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
        'description',
        'input_field_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getInputFieldIdAttribute($value)
    {
        $fields = json_decode($value, true);

        return InputFields::whereIn('id', $fields);
    }

    public function answers()
    {
        return $this->hasMany(Answers::class, 'question_id');
    }
}
