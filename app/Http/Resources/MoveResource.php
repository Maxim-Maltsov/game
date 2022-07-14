<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class  MoveResource extends JsonResource
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
            'game_id' => $this->game_id,
            'round_id' => $this->round_id,
            'player_id' => UserResource::make($this->player), // $this->player - is Relationship(Получаем все поля с данными игрока из таблицы пользователей). $this->player_id получаем id игрока.
            'figure' => $this->figure,
            'created_at' => $this->created_at,
        ];
    }
}
