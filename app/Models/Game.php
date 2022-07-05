<?php

namespace App\Models;

use App\Http\Resources\GameResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
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


    public static function showWaitingBlock(): bool
    {
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_1', Auth::id())
                    ->first();
        
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function showOfferBlock(): bool
    {
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_2', Auth::id())
                    ->first();
       
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function showGameplayBlock(): bool
    {
        $game = Game::where('status', [Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', '=', Auth::id());
                        $query->orWhere('player_2', '=', Auth::id());
                    })->first();
                
       
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }




    public static function init()
    {   


        $game = Game::whereIn('status',[Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', '=', Auth::id());
                        $query->orWhere('player_2', '=', Auth::id());
                    })->first();
        

        if ( $game == null) {

            return response()->json([ 'data' => [

                'message' => 'The Game Was Not Found! Start the game!',
            ]]);
        }
    
        return response()->json([ 'data' => [

            'game' => GameResource::make($game),
            'waiting' => Game::showWaitingBlock(),
            'offer' => Game::showOfferBlock(),
            'play' => Game::showGameplayBlock(),
            'leave' => Game::showGameplayBlock(),
            'playing' => User::canPlay(Auth::id()),
        ]]);
    }

    
}
