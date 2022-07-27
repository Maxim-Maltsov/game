<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
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
            'game_id' => $this->game_id,
            'round_number' => $this->round_number,
            'move_player_1' => $this->move_player_1,
            'move_player_2' => $this->move_player_2,
            'winned_player' => UserResource::make($this->winnedPlayer),
            'draw' => $this->draw,
            'timeout' => $this->timeout,
            'created_at' => $this->created_at,
        ];
    }
}
