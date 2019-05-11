<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Answers
 * @package App\Models
 * @property integer user_id
 * @property integer question_id
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
        'question_id',
        'answer',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }
}
