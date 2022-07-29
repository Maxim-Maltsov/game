<?php

namespace App\Jobs;

use App\Events\GameEndEvent;
use App\Events\GameFinishEvent;
use App\Events\GameNewRoundStartEvent;
use App\Events\PlayersDidNotMakeMovesEvent;
use App\Exceptions\MoveAlreadyMadeException;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\GameResource;
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
        echo " 1. Оброболтчик игрового процесса запущен. Время до окончания раунда = $roundEndTime ";

        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
        
            if ( $currentTime <=  $roundEndTime) {

                if ($this->canFinishGame()) {
                    
                    echo " 4. Можно завершить игру \n";
                    $this->finishGame();
                    echo " 5. Игра завершена \n";
                    break;
                }


                if ($this->canRestartTimer()) { 

                    $this->restartTimer();

                    echo " Таймер перезапущен. \n";
                }
                else if ($this->canStartNewRound($currentTime,  $roundEndTime)) {

                    if ($this->endRoundReason == Game::ROUND_TIME_IS_UP) {

                        echo " Условие запуска метода выполнения хода за игрока сработало. \n";
                        try {
                            
                            $this->makeMoveIfNeeded();

                            echo " 2. Завершение ходов makeMoveIsNeeded() выполнено. \n";
                        }
                        catch (MoveAlreadyMadeException $e) {

                            // Log::info($e->getMessage());
                        } 
                    }
                    
                    $this->StartNewRound();
                    echo " 3. Запуск нового раунда ";

                    break;
                }
            }

            sleep(1);
        }
    }


    public function canRestartTimer() {

        // Если оба игрока не сделали ходов, то нужен перезапуск.
        
        // 1. Получить игру.
        $game = Game::where('id', $this->game->id)->first();
        // 2. Получить метку необходимости перезапустить таймер
        $needRestartTimer = $game->need_restart_timer;
        // 3. Проверить условие, при котором нужен перезапуск.
        if ($needRestartTimer == Game::YES) {
            
            echo " Условие перезапуска Таймера сработало и равно true! \n";
            return true;
        }
    }


    public function restartTimer() {

        // 1. Получить игру
        $game = Game::where('id', $this->game->id)->first();
        // 2. Сделать метку необходимости перезапустить таймер равной 0.
        $game->need_restart_timer = Game::NO;
        $game->save();

        // 3. Получить активный раунд 
        $activeRound = $game->getActiveRound();
        // 4.Обновляем время создания раунда на текущее время.
        $activeRound->created_at = Carbon::now();
        $activeRound->save();
        
        // 5. Запустить GameProcessJob::dispatch($game);
        GameProcessJob::dispatch($game);
        // 6. Запустить событие говорящее о том, что игроки не сделали ходов.
        PlayersDidNotMakeMovesEvent::dispatch(GameResource::make($game));
        echo "Метод перезапуска Таймера Сработал \n";
    }

    
    public function canStartNewRound($currentTime, $endTime)
    {   
        $game = Game::where('id', $this->game->id)->first();
        $needStartNewRound = $game->need_start_new_round;

        if ($needStartNewRound == Game::YES) { // Разрешение на начало нового раунда, если оба игрока сделали ход.
            
            $this->endRoundReason = Game::ALL_PLAYERS_MADE_MOVE;
           
            return true;
        }
           
        if ($currentTime >= $endTime) { // Разрешение на то, чтобы сделать ходы за игроков, если истекло время предыдущего раунда и ход какого-то игрока не сделан.

            $this->endRoundReason = Game::ROUND_TIME_IS_UP;
            print( "Время раунда вышло  \n");

            return true;
        }
    }


    public function makeMoveIfNeeded()
    {  
        echo "метод makeMoveIsNeeded запущен \n";

        $game = Game::where('id', $this->game->id)->first();

        // Делаем метку перезапуска таймера активной;
        $game->makeTimerRestartActiveIfNeeded();

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;

        $players = [$firstPlayer, $secondPlayer];
        // var_dump(  $players);

        $activeRound = $game->getActiveRound();
        
        foreach ($players as $player) {

            $move = Move::where('game_id', $game->id)->where('player_id', $player->id )->first();
            
            if ($move instanceof Move) {

                throw new MoveAlreadyMadeException(" $player->name has already made a move in $activeRound->number round game with id:$game->id . ");
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


    public function StartNewRound()
    {   
        $round = new Round();
        $round->game_id = $this->game->id;
        $round->number = $this->game->getLastFinishedRound()->number + 1; // 
        $round->save();

        $game = Game::where('id', $this->game->id)->first(); // Игра с обновлённым состоянием.

        $game->need_start_new_round = Game::NO;
        $game->save(); 

        GameProcessJob::dispatch($game);
        GameNewRoundStartEvent::dispatch(GameResource::make($game));
    }


    public function canFinishGame()
    {   
        $game = Game::where('id', $this->game->id)->first();

        $victoriesPlayers = $game->getAllVictoriesPlayersInRounds();

        foreach($victoriesPlayers as $victoriesPlayer )
        {
            $victories = $victoriesPlayer->victories;
           
            if ($victories == Game::VICTORY_CONDITION){

                return  true;
            }
        }
    }


    public function finishGame()
    {
        $game = Game::where('id', $this->game->id)->first();

        $winnedPlayer = $game->defineWinnerGame();

        if ( $winnedPlayer == null) {

            echo "Не удалось определить победителя \n";
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
    }
}
