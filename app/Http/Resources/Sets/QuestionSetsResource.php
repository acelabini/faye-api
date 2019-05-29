<?php

namespace App\Http\Resources\Sets;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionSetsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            =>  $this->id,
            'title'         =>  $this->title,
            'description'   =>  $this->description,
            'status'        =>  $this->status,
            'questions'     =>  $this->generate_questionnaires
        ];
    }
}