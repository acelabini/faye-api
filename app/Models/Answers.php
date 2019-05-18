<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Answers
 * @package App\Models
 * @property integer user_id
 * @property string device_address
 * @property integer questionnaire_id
 * @property integer field_id
 * @property string answer
 * @property \DateTime created_at
 * @property \DateTime deleted_at
 */
class Answers extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'device_address',
        'questionnaire_id',
        'field_id',
        'answer',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questionnaire()
    {
            return $this->belongsTo(QuestionnaireSets::class, 'questionnaire_id');
    }
    
    public function field()
    {
        return $this->belongsTo(InputFields::class, 'field_id');
    }
}
