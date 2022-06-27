<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GameResource extends JsonResource
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

            'id' => $this->id,
            'player_1' => $this->player_1,
            'player_2' => $this->player_2,
            'start' => $this->start,
            'end' => $this->end,
            'winner' => $this->winner,
            'leaving_player' => $this->leaving_player,
            'created_at' => $this->created_at,
        ];
    }
}
