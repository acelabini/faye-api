<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class QuestionSetsResource
 * @package App\Models
 * @property int created_by
 * @property string title
 * @property string description
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class QuestionSets extends Model
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

    public function questionnaires()
    {
        return $this->hasMany(QuestionnaireSets::class, 'set_id')->orderBy('order', 'asc');
    }

    public function location()
    {
        return $this->belongsTo(LocationBarangays::class, 'location_id');
    }

    public function getGenerateQuestionnairesAttribute()
    {
        $questions = [];
        foreach ($this->questionnaires as $questionnaire) {
            $questions[] = [
                'order'     =>  $questionnaire->order,
                'question'  =>  [
                    'title'             =>  $questionnaire->question->title,
                    'description'       =>  $questionnaire->question->description,
                    'inputs'            =>  $questionnaire->question->generate_inputs
                ]
            ];
        }

        return $questions;
    }
}
