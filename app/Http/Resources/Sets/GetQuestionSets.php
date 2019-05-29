<?php

namespace App\Http\Resources\Sets;

use Illuminate\Http\Resources\Json\JsonResource;

class GetQuestionSets extends JsonResource
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
        foreach ($this as $sets) {
            foreach ($sets as $set) {
                $result[] = [
                    'id' => $set->id,
                    'created_by'    =>  $set->user->name,
                    'title' => $set->title,
                    'description' => $set->description,
                    'location'  =>  $set->location_id ? $set->location : null,
                    'status'    =>  $set->status
                ];
            }
        }

        return $result;
    }
}