<?php

namespace App\Http\Resources\Hazards;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationHazard extends JsonResource
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
            'location_id'   =>  $this->location_id,
            'location_name' =>  optional($this->location)->name,
            'hazard_id'     =>  $this->hazard_id,
            'hazard_name'   =>  optional($this->hazard)->name,
            'status'        =>  $this->status,
            'created_at'    =>  $this->created_at->format('F-d-Y H:i:s')
        ];
    }
}