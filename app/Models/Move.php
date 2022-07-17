<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    use HasFactory;

    protected $fillable = [ 'game_id', 'round_number', 'figure'];  

    // Relationship.

    public function game()
    {
        return  $this->belongsTo(Game::class); // Получаемм игру к которой принадлежит ход.
    }

    public function round()
    {
        return  $this->belongsTo(Round::class); // Получаемм раунд которому принадлежит ход.
    }

    public function player() // Получаемм игрока которой сделал ход.
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    // Methods.
}
