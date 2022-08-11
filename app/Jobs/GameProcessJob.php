<?php

namespace App\Jobs;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Events\GameFinishEvent;
use App\Events\GameNewRoundStartEvent;
use App\Events\RoundTimerRestartEvent;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use App\Models\Game;
use App\Models\Move;
use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Psy\Exception\BreakException;

class GameProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $game;
    public $endRoundReason;
    public $needRestartTimer = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $roundEndTime = $this->game->getRoundEndTime();

        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
        
            if ( $currentTime <=  $roundEndTime) {

                if ($this->canFinishGame()) {
                    
                    $this->finishGame();
                    break;
                }

                if ($this->leavedGame()) {

                    break;
                }

                if ($this->canStartNewRound($currentTime,  $roundEndTime)) {

                    if ($this->endRoundReason == Game::ROUND_TIME_IS_UP) {

                        $this->makeMoveIfNeeded();
                    }

                    $this->startNewRound();
                    break;
                } 
                
                if ($this->needRestartTimer) {

                    $this->restartTimer();
                    break;
                }
            }

            sleep(1);
        }
    }

    
    public function makeMoveIfNeeded(): void
    {   
        // Обновление состояния модели. Метод fresh() загружает новый экземпляр модели из базы данных с обнавлёнными атрибутами.
        $game = $this->game->fresh();

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;

        $players = [$firstPlayer, $secondPlayer];
        $activeRound = $game->getActiveRound();
        
        foreach ($players as $player) {
            
            
            $move = $game->getMovePlayerInActiveRound($player, $activeRound);
            
            if ($move instanceof Move) {

                continue;
            }
            
            $move = new Move();
            $move->game_id = $game->id;
            $move->round_number = $activeRound->number;
            $move->player_id = $player->id;
            $move->figure = Game::FIGURE_NONE;
            $move->save();

            $game->finishRoundIfNeeded();
        }
    }


    public function canStartNewRound($currentTime, $endTime): bool 
    {   
        $game = $this->game->fresh();
        
        $needStartNewRound = $game->need_start_new_round;

        if ($needStartNewRound == Game::YES) { 
            
            $this->endRoundReason = Game::ALL_PLAYERS_MADE_MOVE;
           
            return true;
        }
           
        if ($currentTime >= $endTime) { 
            if ($game->playersNotMakeMoves()) {
                
                $this->needRestartTimer = true;
               
                return false;
            }

            $this->endRoundReason = Game::ROUND_TIME_IS_UP;
           
            return true;
        }

        return false;
    }


    public function startNewRound(): void
    {   
        if ($this->canFinishGame()) {

            $this->finishGame();

            return;
        }

        $round = new Round();
        $round->game_id = $this->game->id;
        $round->number = $this->game->getLastFinishedRoundNumber() + 1;
        $round->save();

        $game = $this->game->fresh(); // Игра с обновлённым состоянием.
        $game->need_start_new_round = Game::NO;
        $game->save(); 

        GameProcessJob::dispatch($game);
        GameNewRoundStartEvent::dispatch(GameResource::make($game));
    }


    public function restartTimer(): void
    {
        $this->needRestartTimer = false;

        $game = $this->game->fresh();
        
        $activeRound = $game->getActiveRound();
        $activeRound->created_at = Carbon::now();
        $activeRound->save(); 

        GameProcessJob::dispatch($game);
        RoundTimerRestartEvent::dispatch(GameResource::make($game));
    }


    public function canFinishGame(): bool
    {   
        $game = $this->game->fresh();

        $victoriesPlayers = $game->getAllVictoriesPlayersInRounds();

        foreach($victoriesPlayers as $victoriesPlayer )
        {
            $victories = $victoriesPlayer->victory_count;
           
            if ($victories == env('VICTORY_CONDITION')){

                return  true;
            }
        }

        return false;
    }


    public function finishGame(): void
    {
        $game = $this->game->fresh();

        $winnedPlayer = $game->defineWinnerGame();

        if ( $winnedPlayer == null) {

            Log::error('Failed to define the winner');

            return;
        }

        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->winned_player = $winnedPlayer;
        $game->need_start_new_round = Game::NO;
        $game->save();

        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::FREE;
        $firstPlayer->save();

        $secondPlayer = $game->secondPlayer;
        $secondPlayer->game_status = User::FREE;
        $secondPlayer->save();

        GameFinishEvent::dispatch(GameResource::make($game));

        $users = User::getOnlineUsersPaginate(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
    }


    public function leavedGame(): bool
    {
        $game = $this->game->fresh();
        $leavingPlayer = $game->leaving_player;
        
        if ($leavingPlayer != null) {

            return true;
        }

        return false;
    }
}
