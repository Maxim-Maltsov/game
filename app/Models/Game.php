<?php

namespace App\Models;

use App\Events\GameRoundFinishedEvent;
use App\Exceptions\GameNotFoundException;
use App\Http\Resources\GameResource;
use App\Services\GameFieldManagementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class Game extends Model
{
    use HasFactory;

    // Game Status.

    const WAITING_PLAYER = 0;
    const IN_PROCESS = 1;
    const FINISHED = 2;

    // Game Figure.

    const FIGURE_NONE = 0;
    const FIGURE_ROCK = 1;
    const FIGURE_SCISSORS = 2;
    const FIGURE_PAPER = 3;
    const FIGURE_LIZARD = 4;
    const FIGURE_SPOCK = 5;

    // Game Settings.
    const ROUND_TIME_IN_SECONDS = 80;
    const VICTORY_CONDITION = 5;

    // Boolean Value.
    const YES = 1;
    const NO = 0;

    const NO_WINNER = 0;

    const ROUND_TIME_IS_UP = 0;
    const ALL_PLAYERS_MADE_MOVE = 1;


    protected $fillable = [ 'player_1', 'player_2'];


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

    public static function init(): JsonResponse
    {   
        $game = Game::where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->latest()->first();


        if ($game == null) {

            throw new GameNotFoundException('Game not found! Start a new game!');
        }

        return response()->json([ 'data' => [

            'game' => GameResource::make($game),
            'waiting' => GameFieldManagementService::needShowPlayerWaitingBlock(),
            'offer' => GameFieldManagementService::needShowBlockWithOfferToPlay(),
            'play' => GameFieldManagementService::needShowGameFieldBlock(),
            'leave' => GameFieldManagementService::needShowButtonLeaveGame(),
        ]]);
    }

    
    public function checkingFinishGame(): bool
    {
        $game = Game::where('id', $this->id)->first();

        if ($game->status == Game::FINISHED) {
            
            return true;
        }
        
        return false;
    }

    public function getRemainingTimeOfRound(): int               
    {
        $activeRound = $this->getActiveRound();

        if ($activeRound == null) {

            return 0;
        }

        $currentTime = Carbon::now();
        $roundStartTime = $activeRound->created_at;
        $roundEndTime = $roundStartTime->copy()->addSeconds(Game::ROUND_TIME_IN_SECONDS);

        $remainingTime = $currentTime->diffInSeconds($roundEndTime, false);

        if ($remainingTime <= 0) {

            return 0;
        }

        return $remainingTime;
    }

    
    public function getRoundEndTime(): int 
    {
        $activeRound = $this->getActiveRound();

        if ($activeRound == null) {

            return 0;
        }

        $roundStartTime = $activeRound->created_at;
        $roundEndTime = $roundStartTime->copy()->addSeconds(Game::ROUND_TIME_IN_SECONDS);
        $roundEndTimeInSeconds = $roundEndTime->secondsSinceMidnight();

        return $roundEndTimeInSeconds;
    }

    // Перенести метод получения активного раунда в класс "RoundRepository".
    public function getActiveRound(): ?Round
    {
        $activeRound = Round::where('game_id', $this->id)
                            ->where('status', Round::NO_FINISHED)
                            ->first();

        return $activeRound;
    }


    public function getMovesOfActiveRound()//: Collection
    {
        $activeRound = $this->getActiveRound();

        $moves = $activeRound->moves()
                             ->where('game_id', $this->id)
                             ->get();

        return $moves;
    }


    public function getMovePlayerInActiveRound(User $player, Round $activeRound): ?Move
    {
        
        if ( $activeRound == null) {

            Log::error("Active round in the game with id:$this->id not detected.");
            
            return null;
        }

        $move = Move::where('game_id', $this->id)
                    ->where('player_id', $player->id )
                    ->where('round_number', $activeRound->number)
                    ->first();

        return $move;
    }


    public function getLastFinishedRoundNumber(): int
    {   
        $lastFinishedRound = $this->rounds()->where('status', Round::FINISHED)->latest()->first(); // $lastRound получен через связь с game по условию.
       
        if ($lastFinishedRound == null || $this->status == Game::FINISHED) {

            $number = 0;

            return $number;
        }

        return $lastFinishedRound->number;
    }


    public function playersNotMakeMoves(): bool
    {   
        $moves = $this->getMovesOfActiveRound();

        if ($moves->count() == 0) {

            return true;
        }
        
        return false;
    }

    
    public function finishRoundIfNeeded(): void
    {   
        $moves = $this->getMovesOfActiveRound();
         
        if ($moves->count() == 2) {

            $roundMoves = $moves->all();
            $winner_id = $this->defineWinnerRound($roundMoves[0], $roundMoves[1]);

            $winnedPlayer = ($winner_id != Game::NO_WINNER)? $winner_id : null;
            $draw = ($winner_id == Game::NO_WINNER)? Game::YES : Game::NO;
            
            $activeRound = $this->getActiveRound();
            $game = $activeRound->game;

            $activeRound->winned_player = $winnedPlayer;
            $activeRound->draw = $draw;
            $activeRound->status = Round::FINISHED;
            $activeRound->save();

            $game->need_start_new_round = Game::YES;
            $game->save();

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


    public function defineWinnerGame(): int
    {
        $victoriesPlayers = $this->getAllVictoriesPlayersInRounds();

        foreach($victoriesPlayers as $victoriesPlayer )
        {
            $victories = $victoriesPlayer->victory_count;
           
            if ($victories == Game::VICTORY_CONDITION){

                return  $victoriesPlayer->winned_player;
            }
        }
    }


    public function getAllVictoriesPlayersInRounds(): Collection|array
    {
        $game = Game::where('id', $this->id)->first();

        if ($game->status == Game::FINISHED) {

            return $victoriesPlayers = [] ;
        }

        $victoriesPlayers = $this->rounds()
                          ->select(DB::raw('count(*) as victory_count , winned_player'))
                          ->groupBy('winned_player')
                          ->having('winned_player', '!=', 'null')
                          ->orderBy('victory_count', 'desc')
                          ->get();

        return $victoriesPlayers;
    }


    public function getHistoryGame(): SupportCollection
    {
        $historyGame = DB::table('rounds')
                     ->where('rounds.game_id', $this->id )
                     ->where('rounds.status', Round::FINISHED)
                     ->select( 'rounds.game_id', 'rounds.number', 'rounds.winned_player', 'rounds.draw', 'rounds.created_at')
                     ->selectRaw('(SELECT moves.figure FROM moves where moves.game_id = ? AND moves.round_number = rounds.number AND moves.player_id = ? ORDER BY moves.round_number DESC LIMIT 1) as move_player_1 ', [$this->id, $this->player_1])
                     ->selectRaw('(SELECT moves.figure FROM moves where moves.game_id = ? AND moves.round_number = rounds.number AND moves.player_id = ? ORDER BY moves.round_number DESC LIMIT 1) as move_player_2 ', [$this->id, $this->player_2])
                     ->get();
        
        return $historyGame;
    }


    public  function getHistoryLastRound(): stdClass
    {
        $historyLastRound = DB::table('rounds')
                              ->where('rounds.game_id', $this->id )
                              ->where('rounds.status', Round::FINISHED)
                              ->select( 'rounds.game_id', 'rounds.number', 'rounds.winned_player', 'rounds.draw', 'rounds.created_at')
                              ->selectRaw('(SELECT moves.figure FROM moves where moves.game_id = ? AND moves.round_number=rounds.number AND moves.player_id=?  ORDER BY moves.round_number DESC LIMIT 1) as move_player_1', [$this->id, $this->player_1])
                              ->selectRaw('(SELECT moves.figure FROM moves where moves.game_id = ? AND moves.round_number=rounds.number AND moves.player_id=?  ORDER BY moves.round_number DESC LIMIT 1) as move_player_2', [$this->id, $this->player_2])
                              ->orderByDesc('number')
                              ->limit(1)
                              ->first();
       
        if (count((array)$historyLastRound) == 0) {

            $historyLastRound = new stdClass();

            $historyLastRound->game_id = $this->id;
            $historyLastRound->number = 0;
            $historyLastRound->move_player_1 = null;
            $historyLastRound->move_player_2 = null;
            $historyLastRound->winned_player = null;
            $historyLastRound->draw = null;
            $historyLastRound->created_at = Carbon::now();
            
            return $historyLastRound;
        }

        return $historyLastRound;
    }
}
