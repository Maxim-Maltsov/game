<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Round extends Model
{
    use HasFactory;


     // Round Status
     const NO_FINISHED = 0;
     const FINISHED = 1;


     protected $fillable = [ 'game_id', 'round_number'];


    // Relationship.

    public function game() // Получаем игру к которой принадлежит раунд.
    {
        return $this->belongsTo(Game::class); 
    }

    public function moves()  // Получаем все ходы данного раунда.
    {
        return $this->hasMany(Move::class, 'round_number', 'number');
    }

    public function winnedPlayer() // Получаем все данные победившего игрока в данном раунде из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'winned_player');
    }
}
