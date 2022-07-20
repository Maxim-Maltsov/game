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
use Illuminate\Support\Facades\Cookie;

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

    public function firstPlayer() // Получаем все данные первого игрока данной игры из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'player_1');
    }

    public function secondPlayer() // Получаем все данные второго игрока данной игры из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'player_2');
    }

    public function winnedPlayer() // Получаем все данные победившего в данной игре игрока из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'winned_player');
    }

    public function leavingPlayer() // Получаем все данные покинувшего игру игрока из таблицы 'users'.
    {
        return $this->belongsTo(User::class, 'leaving_player');
    }

    public function rounds() // Получаем все раунды относящиеся к данной игры.
    {
        return $this->hasMany(Round::class);
    }

    public function moves() // Получаем все ходы относящиеся к данной игры.
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
        $activeRound = $this->getActiveRound();

        if ($activeRound == null) {

            return 0;
        }

        $currentTime = Carbon::now();
        $roundStartTime = $activeRound->created_at;
        $roundEndTime = $roundStartTime->copy()->addSeconds(env('ROUND_TIME'));

        $remainingTime = $currentTime->diffInSeconds($roundEndTime, false);

        return $remainingTime;
    }


    public function getRoundEndTime() :int 
    {
        $activeRound = $this->getActiveRound();

        if ($activeRound == null) {

            return 0;
        }

        $roundStartTime = $activeRound->created_at;
        $roundEndTime = $roundStartTime->copy()->addSeconds(env('ROUND_TIME'));
        $roundEndTimeInSeconds = $roundEndTime->secondsSinceMidnight();

        return $roundEndTimeInSeconds;
    }


    public function getMovesOfActiveRound(int $round)
    {
        $moves = Move::where('game_id', $this->id)
                     ->where('round_number', $round)
                     ->get();

        return $moves;
    }


    public function getActiveRound() :?Round
    {
        $activeRound = $this->rounds()->where('status', Round::NO_FINISHED)->first(); // $activeRound получен через связь с game по условию.

        return $activeRound;
    }


    public function getLastFinishedRound() :?Round
    {
        $lastRound = $this->rounds()->where('status', Round::FINISHED)->first(); // $lastRound получен через связь с game по условию.

        return $lastRound;
    }


    public function finishRoundIfNeeded(MoveRequest $request)
    {   
        $moves = $this->getMovesOfActiveRound($request->validated(['round_number']));

        if ($moves->count() == 2) {

            $roundMoves = $moves->all();
            $winner_id = $this->defineWinner($roundMoves[0], $roundMoves[1]);

            $winnedPlayer = ($winner_id != Game::NO_WINNER)? $winner_id : null;
            $draw = ($winner_id == Game::NO_WINNER)? Game::YES : Game::NO;
            
            $activeRound = $this->getActiveRound();

            $game = $activeRound->game;
            $game->need_start_new_round = Game::YES;
            $game->save();

            $activeRound->winned_player = $winnedPlayer;
            $activeRound->draw = $draw;
            $activeRound->status = Round::FINISHED;
            $activeRound->save();

            GameRoundFinishedEvent::dispatch(GameResource::make($this));
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
    }


}
