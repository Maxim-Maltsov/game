<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

     // Game status.
     const WAITING_PLAYER = 0;
     const IN_PROCESS  = 1;
     const FINISHED  = 2;


     protected $fillable = [ 'player_2'];

}
