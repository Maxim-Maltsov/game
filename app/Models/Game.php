<?php

namespace App\Models;

use App\Events\GameRoundFinishedEvent;
use App\Exceptions\GameNotFoundException;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\GameResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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

    // Boolean value.
    const YES = 1;
    const NO = 0;

    const NO_WINNER = 0;
 

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
        ]]);
    }


    public function getRemainingTimeOfRound():int               
    {
        $nowTime = Carbon::now();
        $roundTime = Carbon::createFromTimestampUTC($this->last_round_start)->addSeconds(env('ROUND_TIME'));

        $remainingTime = $nowTime->diffInSeconds($roundTime, false);

        $remainingTime = Carbon::createFromTimestampUTC($remainingTime)->secondsSinceMidnight();
    
        return $remainingTime;
    }


    public function getMovesOfRound(int $round)
    {
        $moves = Move::where('game_id', $this->id)
                     ->where('round', $round)
                     ->where('finished', 0)
                     ->get();

        return $moves;
    }


    public function finishRoundIsNeeded(MoveRequest $request)
    {   
        $moves = $this->getMovesOfRound($request->validated(['round']));

        if ($moves->count() == 2) {

            $roundMoves = $moves->all();
            $winner_id = $this->defineWinner($roundMoves[0], $roundMoves[1]);
            
            foreach ($moves as $move) {

                $move->winner = ($winner_id == $moves->player_id)? Game::YES : Game::NO;
                $move->draw = ($winner_id == Game::NO_WINNER)? Game::YES : Game::NO;
                $move->finished = Game::YES;
                $move->save();
            }

            // Событие завершение раунда.
            // GameRoundFinishedEvent::dispatch();
        }
    }


    public function defineWinner(Move $move_1, Move $move_2): int
    {
        $figure_1 = $move_1->figure;
        $figure_2 = $move_2->figure;

        if ($figure_1 == Game::FIGURE_NONE) {
            
            switch ($figure_2) {
                
                case Game::FIGURE_NONE:
                    return 0;

                case Game::FIGURE_ROCK:
                case Game::FIGURE_SCISSORS:
                case Game::FIGURE_PAPER:
                case Game::FIGURE_LIZARD:
                case Game::FIGURE_SPOCK:
                    return $move_2->player_id;
            }

        } elseif ($figure_1 == Game::FIGURE_ROCK) {

            switch ($figure_2) {
                
                case Game::FIGURE_ROCK:
                    return 0;

                case Game::FIGURE_NONE:
                case Game::FIGURE_SCISSORS:
                case Game::FIGURE_LIZARD:
                    return $move_1->player_id;

                case Game::FIGURE_PAPER:
                case Game::FIGURE_SPOCK:
                    return $move_2->player_id;
            }

        } elseif ($figure_1 == Game::FIGURE_SCISSORS) {

            switch ($figure_2) {
                
                case Game::FIGURE_SCISSORS:
                    return 0;

                case Game::FIGURE_NONE:
                case Game::FIGURE_PAPER:
                case Game::FIGURE_LIZARD:
                    return $move_1->player_id;

                case Game::FIGURE_ROCK:
                case Game::FIGURE_SPOCK:
                    return $move_2->player_id;
            }

        } elseif ($figure_1 == Game::FIGURE_PAPER) {

            switch ($figure_2) {

                case Game::FIGURE_PAPER:
                    return 0;

                case Game::FIGURE_NONE:
                case Game::FIGURE_ROCK:
                case Game::FIGURE_SPOCK:
                    return $move_1->player_id;

                case Game::FIGURE_SCISSORS:
                case Game::FIGURE_LIZARD:
                    return $move_2->player_id;
            }

        } elseif ($figure_1 == Game::FIGURE_LIZARD) {

            switch ($figure_2) {
        
                case Game::FIGURE_LIZARD:
                    return 0;

                case Game::FIGURE_NONE:
                case Game::FIGURE_PAPER:
                case Game::FIGURE_SPOCK:
                    return $move_1->player_id;

                case Game::FIGURE_ROCK:
                case Game::FIGURE_SCISSORS:
                    return $move_2->player_id;
            }

        } elseif ($figure_1 == Game::FIGURE_SPOCK) {

            switch ($figure_2) {
                
                case Game::FIGURE_SPOCK:
                    return 0;

                case Game::FIGURE_NONE:
                case Game::FIGURE_ROCK:
                case Game::FIGURE_SCISSORS:
                    return $move_1->player_id;

                case Game::FIGURE_PAPER:
                case Game::FIGURE_LIZARD:
                    return $move_2->player_id;
            }
        }
          
        // return 0;
    }


}
