<?php

namespace App\Http\Resources\Answers;

use Illuminate\Http\Resources\Json\JsonResource;

class GetAnswer extends JsonResource
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
        foreach ($this as $answers) {
            foreach ($answers as $answer) {
                $result[] = [
                    'field_name'    =>  $answer->field->name,
                    'answer'        =>  $answer->answer
                ];
            }
        }

        return $result;
    }
}