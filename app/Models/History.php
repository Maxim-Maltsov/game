<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;


    // Relationship.

    public function game() // Получаем игру к которой принадлежит история.
    {
        return $this->belongsTo(Game::class); 
    }

    public function winnedPlayer() // Получаем все данные победившего игрока в данной истории раунда из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'winned_player');
    }

}
