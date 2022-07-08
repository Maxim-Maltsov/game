<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    use HasFactory;

    protected $fillable = [ 'game_id', 'round', 'figure'];

    // Relationship.

    public function game()
    {
        return  $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    // Methods.
}
