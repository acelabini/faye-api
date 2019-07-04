<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class Users extends JsonResource
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
        foreach ($this as $users) {
            foreach ($users as $user) {
                $result[] = [
                    'id'            =>  $user->id,
                    'name'          =>  $user->name,
                    'email'         =>  $user->email,
                    'role'          =>  $user->role_id,
                    'role_name'     =>  ucfirst($user->role->name),
                    'status'        =>  $user->status,
                    'status_name'   =>  ucfirst($user->status),
                    'created_at'    =>  $user->created_at->format('M-d-Y H:i:s'),
                ];
            }
        }

        return $result;
    }
}