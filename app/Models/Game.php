<?php

namespace App\Models;

use App\Http\Resources\GameResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Game extends Model
{
    use HasFactory;

     // Game status.
     const WAITING_PLAYER = 0;
     const IN_PROCESS  = 1;
     const FINISHED  = 2;

    // Game figure.
     const FIGURE_NONE = 0;
     const FIGURE_STONE = 1;
     const FIGURE_SCISSORS = 2;
     const FIGURE_PAPER = 3;
     const FIGURE_LIZARD = 4;
     const FIGURE_SPOCK = 5;
 

     protected $fillable = [ 'player_2'];

    
    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'player_1');
    }

    
    public function  secondPlayer()
    {
        return $this->belongsTo(User::class, 'player_2');
    }

    public static function getGame()
    {
        $game = Game::whereIn('status',[Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', '=', Auth::id());
                        $query->orWhere('player_2', '=', Auth::id());
                    })->first();


        if ($game instanceof Game) {
            
            return GameResource::make($game);
        }

        
        return response()->json([ 'data' => [

            'exception' => 'Active Game Not Found!',
        ]]);
    }
}
