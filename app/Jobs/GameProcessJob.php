<?php

namespace App\Jobs;

use App\Events\GameEndEvent;
use App\Events\GameFinishEvent;
use App\Events\GameNewRoundStartEvent;
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
        echo " 1. Получение времени окончания раунда getRoundEndTime() $roundEndTime ";

        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
        
            if ( $currentTime <=  $roundEndTime) {

                if ($this->canFinishGame()) {
                    
                    echo " 4. Можно завершить игру \n";
                    $this->finishGame();
                    echo " 5. Игра завершена \n";
                    break;
                }

                if ($this->canStartNewRound($currentTime,  $roundEndTime)) {

                    if ($this->endRoundReason == Game::ROUND_TIME_IS_UP) {

                        echo " Условие запуска метода выполнения хода за игрока сработало. \n";
                        try {
                            
                            $this->makeMoveIsNeeded($this->game);
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

    
    public function canStartNewRound($currentTime, $endTime)
    {   
        // Разрешение на начало нового раунда, если оба игрока сделали ход. 
        $game = Game::where('id', $this->game->id)->first();
        $needStartNewRound = $game->need_start_new_round;

        if ($needStartNewRound == Game::YES) {
            
            $this->endRoundReason = Game::ALL_PLAYERS_MADE_MOVE;
           
            return true;
        }
           
        // Разрешение на то, чтобы сделать ходы за игроков, если истекло время предыдущего раунда и ход какого-то игрока не сделан.
        if ($currentTime >= $endTime) {

            print_r( "Условие при котором выполняются ходы за игрока $currentTime >= $endTime \n");
            
            $this->endRoundReason = Game::ROUND_TIME_IS_UP;
            print( "Сейчас причина для начала хода за игрока  endRoundReason значение должно быть 0,  оно = $this->endRoundReason \n");
            return true;
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


    public function makeMoveIsNeeded(Game $game)
    {   echo "метод makeMoveIsNeeded запущен \n";
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

            $request = new MoveRequest(['round_number' => $activeRound->number]);
        
            $game->finishRoundIfNeeded($request);
        }
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
