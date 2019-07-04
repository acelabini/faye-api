<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'email'         =>  $this->email,
            'role'          =>  $this->role_id,
            'role_name'     =>  ucfirst($this->role->name),
            'status'        =>  $this->status,
            'status_name'   =>  ucfirst($this->status),
            'created_at'    =>  $this->created_at->format('M-d-Y H:i:s'),
        ];
    }
}