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

}
