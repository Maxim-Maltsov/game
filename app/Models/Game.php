<?php

namespace App\Models;

use App\Exceptions\GameNotFoundException;
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
    const IN_PROCESS = 1;
    const FINISHED = 2;

    // Game figure.

    const FIGURE_NONE = 0;
    const FIGURE_ROCK = 1;
    const FIGURE_SCISSORS = 2;
    const FIGURE_PAPER = 3;
    const FIGURE_LIZARD = 4;
    const FIGURE_SPOCK = 5;
 

    protected $fillable = [ 'player_2'];

    // Relationship.

    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'player_1');
    }

    public function secondPlayer()
    {
        return $this->belongsTo(User::class, 'player_2');
    }

    public function winnedPlayer()
    {
        return $this->belongsTo(User::class, 'winned_player');
    }

    public function leavingPlayer()
    {
        return $this->belongsTo(User::class, 'leaving_player');
    }

    public function moves()
    {
        return $this->hasMany(Move::class);
    }


    // Methods.

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
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->first();
                
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function init(): JsonResponse
    {   
        $game = Game::whereIn('status',[Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->first();
        

        if ( $game == null) {

            throw new GameNotFoundException('The Game Was Not Found! Start the game!');
        }
    
        return response()->json([ 'data' => [

            'game' => GameResource::make($game),
            'waiting' => Game::showWaitingBlock(),
            'offer' => Game::showOfferBlock(),
            'play' => Game::showGameplayBlock(),
            'leave' => Game::showGameplayBlock(),
            // 'totalSeconds' => Game::getTotalSeconds(),
        ]]);
    }


    public static function getTotalSeconds()
    {
        //
    }


    public static function movesMade()
    {
        // Проверить оба игрока сделали ход.
    }


    public static function defineWinner()
    {

        // 
    }
    
}
