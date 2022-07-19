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
            'player_1' => UserResource::make($this->firstPlayer),
            'player_2' => UserResource::make($this->secondPlayer),
            'status' => $this->status,
            'start' => $this->start,
            'end' => $this->end,
            'winned_player' => UserResource::make($this->winnedPlayer), // $this->whenLoaded('winnedPlayer')
            'leaving_player' => UserResource::make($this->leavingPlayer), // $this->whenLoaded('leavingPlayer')
            'remainingTimeOfRound' => $this->getRemainingTimeOfRound(),
            'rounds' => RoundResource::collection($this->whenLoaded('rounds')), // $this->whenLoaded('rounds') - получаем все раунды игры через связь, если они есть.
            'lastFinishedRound' => $this->getLastFinishedRound(),
            // 'needStartNewRound' => $this->needStartNewRound,
            'created_at' => $this->created_at,
        ];
    }
}
