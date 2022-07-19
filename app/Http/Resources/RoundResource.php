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

            'id' => $this->id,
            'game_id' => $this->game_id,
            'status' => $this->status,
            'winned_player' => UserResource::make($this->whenLoaded('winnedPlayer')), // Со значением $this->winnedPlayer - получаем все поля c данными пользователя из таблицы 'users'. Со значением $this->winner_player - получаем только id пользователя. 
            'draw' => $this->draw,
            'moves' => MoveResource::make($this->whenLoaded('moves')), // Получаем все ходы раунда через связь, если они есть.
            'created_at' => $this->created_at,
        ];
    }
}
