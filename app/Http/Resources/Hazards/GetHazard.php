<?php

namespace App\Http\Resources\Hazards;

use Illuminate\Http\Resources\Json\JsonResource;

class GetHazard extends JsonResource
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
            'name'          =>  $this->name,
            'created_by'    =>  $this->createdBy->name,
            'status'        =>  $this->status,
            'created_at'    =>  $this->created_at->format('M-d-Y H:i:s')
        ];
    }
}