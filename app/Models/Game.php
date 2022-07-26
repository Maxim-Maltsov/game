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
use Illuminate\Support\Facades\DB;

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

    const ROUND_TIME_IS_UP = 0;
    const ALL_PLAYERS_MADE_MOVE = 1;

    // The condition for winning the game.
    const VICTORY_CONDITION = 2;


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

    public function history() // Получаем коллекцию с историей каждого раунда относящегося к данной игры.
    {
        return $this->hasMany(History::class);
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
        

        if ($game == null) {

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


    public function getRemainingTimeOfRound(): int               
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


    public function getRoundEndTime(): int 
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

   
    public function getActiveRound() :?Round
    {
        $activeRound = $this->rounds()->where('status', Round::NO_FINISHED)->first(); // $activeRound получен через связь с game по условию.

        return $activeRound;
    }


    public function getMovesOfActiveRound(int $round) // Определить возвращаемый тип данных.
    {
        $moves = Move::where('game_id', $this->id)
                     ->where('round_number', $round)
                     ->get();

        return $moves;
    }


    public function getLastFinishedRound() :?Round
    {   
        $rounds = $this->rounds; // $rounds - получение раундов через отношение с game.

        if ($rounds->isEmpty()) {

            $round = new Round();
            $round->id = 1;
            $round->game_id = $this->id;
            $round->number = 0;
            $round->status = Round::FINISHED;
            $round->winned_player = null;
            $round->draw = Game::NO;
            $round->created_at = Carbon::now();
            $round->updated_at = Carbon::now();

            return $round;
        }

        $lastRound = $this->rounds()->where('status', Round::FINISHED)->latest()->first(); // $lastRound получен через связь с game по условию.

        return $lastRound;
    }

    // Возможно не нужен.!!!
    public function getMovesLastFinishedRound()
    {
        $lastRound = $this->getLastFinishedRound();

        $movesLastRound = $this->moves()
                               ->where('round_number', $lastRound->number)
                               ->get();

        return $movesLastRound;
    }

    
    public function finishRoundIfNeeded(MoveRequest $request)
    {   
        $moves = $this->getMovesOfActiveRound($request['round_number']);
         
        if ($moves->count() == 2) {

            $roundMoves = $moves->all();
            $winner_id = $this->defineWinnerRound($roundMoves[0], $roundMoves[1]);

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

            $this->saveHistoryGame($moves, $activeRound, $winnedPlayer, $draw);

            GameRoundFinishedEvent::dispatch(GameResource::make($this));
        }
    }


    public function defineWinnerRound(Move $move_1, Move $move_2): int
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


    public function getAllVictoriesPlayersInRounds(): Collection 
    {
        $victoriesPlayers = $this->rounds()
                          ->select(DB::raw('count(*) as victories, winned_player'))
                          ->groupBy('winned_player')
                          ->having('winned_player', '!=', 'null')
                          ->orderBy('victories', 'desc')
                          ->get();

        return $victoriesPlayers;
    }


    public function defineWinnerGame(): int
    {
        $victoriesPlayers = $this->getAllVictoriesPlayersInRounds();

        foreach($victoriesPlayers as $victoriesPlayer )
        {
            $victories = $victoriesPlayer->victories;
           
            if ($victories == Game::VICTORY_CONDITION){

                return  $victoriesPlayer->winned_player;
            }
        }
    }


   // Возможно не нужен.!!!
    public function getRoundResults()
    {
        // $lastRound = $this->getLastFinishedRound();

        // $moves = $this->getMovesLastFinishedRound();

        // return $moves;
    }


    public function saveHistoryGame( $moves, Round $round, int|null $winned, int $draw): void
    {
        $moveFirstPlayer = ($this->player_1 == $moves[0]->player_id)? $moves[0]->figure : $moves[1]->figure;
        $moveSecondPlayer = ($this->player_2 == $moves[0]->player_id)? $moves[0]->figure : $moves[1]->figure;
        $roundNumber = $round->number;

        $history = new History();
        $history->game_id = $this->id;
        $history->round_number = $roundNumber;
        $history->move_player_1 = $moveFirstPlayer;
        $history->move_player_2 = $moveSecondPlayer;
        $history->winned_player = $winned;
        $history->draw = $draw;
        $history->save();
    }

}
