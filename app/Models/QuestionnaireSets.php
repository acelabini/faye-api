<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class QuestionnaireSets
 * @package App\Models
 * @property int set_id
 * @property int question_id
 * @property int order
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class QuestionnaireSets extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'set_id',
        'question_id',
        'order'
    ];

    public function set()
    {
        return $this->belongsTo(QuestionSets::class, 'set_id');
    }

    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }
}
