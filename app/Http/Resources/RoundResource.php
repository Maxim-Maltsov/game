<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
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
            'game_id' => GameResource::make($this->game),
            'player_id' => UserResource::make($this->player),
            'round' => $this->round,
            'figure' => $this->figure,
            'winner' => $this->winner,
            'draw' => $this->draw,
            'finished' => $this->finished,
            'created_at' => $this->created_at,
        ];
    }
}
