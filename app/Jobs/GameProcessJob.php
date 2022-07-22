<?php

namespace App\Jobs;

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
        print_r(" 1. 1-е Получение времени окончания раунда getRoundEndTime() $roundEndTime ");
        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
        
            if ( $currentTime <=  $roundEndTime) {

                if ($this->canStartNewRound($currentTime,  $roundEndTime)) {

                    if ($this->endRoundReason == Game::ROUND_TIME_IS_UP) {

                        print_r(" Условие запуска метода выполнения хода за игрока сработало. \n");

                        try {
                            
                            $this->makeMoveIsNeeded($this->game);
                            print_r(" 2. Завершение ходов makeMoveIsNeeded() выполнено. \n");
                        }
                        catch (MoveAlreadyMadeException $e) {

                            // Log::info($e->getMessage());
                        } 
                    }
                    
                    print_r(" 3. Запуск нового раунда ");
                    $this->StartNewRound();
                
                    break;
                }
            }

            sleep(1);
        }

        // GameProcessJob::dispatch($this->game);
        // print_r(" 5. Новый запуск GameProcessJob ");
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
        var_dump(  $players);
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
}
