<?php

namespace App\Http\Resources\Questions;

use Illuminate\Http\Resources\Json\JsonResource;

class GetQuestions extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [];
        foreach ($this as $questions) {
            foreach ($questions as $question) {
                $result[] = [
                    'id' => $question->id,
                    'created_by'    =>  $question->user->name,
                    'title' => $question->title,
                    'description' => $question->description
                ];
            }
        }

        return $result;
    }
}