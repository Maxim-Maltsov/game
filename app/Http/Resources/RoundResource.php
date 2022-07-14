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
            'move_player_1' => MoveResource::make($this->whenLoaded('moveFirstPlayer')), // Со значением $this->moveFirstPlayer - получаем все поля хода из таблицы 'moves'. $this->move_player_1 - получаем id хода.
            'move_player_2' => MoveResource::make($this->whenLoaded('moveSecondPlayer')), // Со значением $this->move_player_2 - получаем только id хода.
            'winned_player' => UserResource::make($this->whenLoaded('winnedPlayer')), // Со значением $this->winnedPlayer - получаем все поля c данными пользователя из таблицы 'users'. Со значением $this->winner_player - получаем только id пользователя. 
            'draw' => $this->draw,
            'finish' => $this->finish,
            'created_at' => $this->created_at,
        ];
    }
}
