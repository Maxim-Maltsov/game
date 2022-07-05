<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [

            'id'=> $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'online_status' => $this->online_status,
            'game_status' => $this->game_status,
            'can_play' => $this->canPlay(),
            'created_at' => $this->created_at,
            
        ];
    }
}
