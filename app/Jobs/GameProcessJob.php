<?php

namespace App\Jobs;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Events\GameFinishEvent;
use App\Events\GameNewRoundStartEvent;
use App\Events\RoundTimerRestartEvent;
use App\Exceptions\MoveAlreadyMadeException;
use App\Exceptions\TimerRestartException;
use App\Http\Requests\MoveRequest;
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
use Illuminate\Support\Facades\Cookie;
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
        echo " - Обработчик игрового процесса запущен. Первоночальный запуск в методе handle() \n";

        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
        
            if ( $currentTime <=  $roundEndTime) {

                if ($this->canFinishGame()) {
                    
                    $this->finishGame();
                    echo " - Игра завершена. finishGame() \n";
                    break;
                }

                if ($this->leavedGame()) {

                    echo " - Игрок покинул игру leavedGame() \n";
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


    public function restartTimer()
    {
        $this->needRestartTimer = false;

        // 1. Получить игру
        $game = Game::where('id', $this->game->id)->first();
        
        // 3. Получить активный раунд 
        $activeRound = $game->getActiveRound();
        
        // 4.Обновляем время создания раунда на текущее время.
        $activeRound->created_at = Carbon::now();
        $activeRound->save(); 
        echo " - Время начала раунда обновлено и равно " . Carbon::now() . ". restartTimer() \n";
    
        // 5. Запустить GameProcessJob::dispatch($game);
        GameProcessJob::dispatch($game);
        echo " - GameProcessJob для текущего раунда перезапущен. restartTimer() \n";
        // 6. Запустить событие перезапуска таймера.
        RoundTimerRestartEvent::dispatch(GameResource::make($game));
        echo " - Таймер перезапущен. restartTimer() \n";
    }

    
    public function canStartNewRound($currentTime, $endTime): bool 
    {   
        $game = Game::where('id', $this->game->id)->first();
        $needStartNewRound = $game->need_start_new_round;

        if ($needStartNewRound == Game::YES) { // Разрешение на начало нового раунда, если оба игрока сделали ход.
            
            $this->endRoundReason = Game::ALL_PLAYERS_MADE_MOVE;
           
            return true;
        }
           
        if ($currentTime >= $endTime) { // Разрешение на то, чтобы сделать ходы за игроков, если истекло время предыдущего раунда и ход какого-то игрока не сделан.

            if ($game->playersNotMakeMoves()) {
                
                echo " - Игроки не cделал ходов. Новый раунд не нужен. Возвращён false. canStartNewRound() \n";

                // Сдеолать метку на перезапуск раунда активной.
                $this->needRestartTimer = true;
                echo " - Нужен перезапуск Таймера. Переменная needRestartTimer = true. canStartNewRound() \n";
                
                return false;
            }

            $this->endRoundReason = Game::ROUND_TIME_IS_UP;
            echo " - Время раунда вышло  \n";

            return true;
        }

        return false;
    }


    public function makeMoveIfNeeded()
    {   echo " - Метод завершения ходов запущен. makeMoveIfNeeded() \n";

        $game = Game::where('id', $this->game->id)->first();

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;

        $players = [$firstPlayer, $secondPlayer];

        $activeRound = $game->getActiveRound();
        
        foreach ($players as $player) {
            
            // Получить ход активного раунда.
            // $move = $game->getMovePlayerInActiveRound($player);
            $move = Move::where('game_id', $game->id)
                        ->where('player_id', $player->id )
                        ->where('round_number', $activeRound->number)
                        ->first();
            
            if ($move instanceof Move) {

                echo " - $player->name уже сделал ход в Раунде:$activeRound->number Игры с id:$game->id. makeMoveIfNeeded() . \n";

                continue;
            }
            
            $move = new Move();
            $move->game_id = $game->id;
            $move->round_number = $activeRound->number;
            $move->player_id = $player->id;
            $move->figure = Game::FIGURE_NONE;
            $move->save();

            echo " - Сделан ход за $player->name в Раунде:$activeRound->number Игры с id:$game->id. makeMoveIfNeeded() . \n";

            $game->finishRoundIfNeeded();
        }
    }


    public function startNewRound()
    {   
        if ($this->canFinishGame()) {

            echo " - Побед достаточно. Cоздавать новый раунд не нужно. startNewRound() \n";

            $this->finishGame();

            return;
        }

        $round = new Round();
        $round->game_id = $this->game->id;
        $round->number = $this->game->getLastFinishedRound()->number + 1; // Сделать проверку- Если завершённого раунда ещё нет то возвращать.
        $round->save();

        echo " - Новый раунд создан. startNewRound() \n";

        $game = Game::where('id', $this->game->id)->first(); // Игра с обновлённым состоянием.
        $game->need_start_new_round = Game::NO;
        $game->save(); 

        GameProcessJob::dispatch($game);
        echo " - GameProcessJob для нового раунда перезапущен. StartNewRound() \n";
        GameNewRoundStartEvent::dispatch(GameResource::make($game));
    }


    public function canFinishGame(): bool
    {   
        $game = Game::where('id', $this->game->id)->first();

        $victoriesPlayers = $game->getAllVictoriesPlayersInRounds();

        foreach($victoriesPlayers as $victoriesPlayer )
        {
            $victories = $victoriesPlayer->victory_count;
           
            if ($victories == Game::VICTORY_CONDITION){

                return  true;
            }
        }

        return false;
    }


    public function finishGame()
    {
        $game = Game::where('id', $this->game->id)->first();

        $winnedPlayer = $game->defineWinnerGame();

        if ( $winnedPlayer == null) {

            Log::error('Failed to define the winner');
            echo "Не удалось определить победителя \n";

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
        $game = Game::where('id', $this->game->id)->first();
        $leavingPlayer = $game->leaving_player;
        
        if ($leavingPlayer != null) {

            return true;
        }

        return false;
    }
}
