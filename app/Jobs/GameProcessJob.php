<?php

namespace App\Jobs;

use App\Events\GameNewRoundStartEvent;
use App\Exceptions\MoveAlreadyMadeException;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Move;
use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cookie;
use Psy\Exception\BreakException;

class GameProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $game;
    public $reasonEndRound;

    const ROUND_TIME_IS_UP = 0;
    const ALL_PLAYERS_MADE_MOVE = 1;


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
            $remainingTimeOfRound = $this->game->getRemainingTimeOfRound();
            
            if ( $currentTime <= $roundEndTime) {

                $game = Game::where('id', $this->game->id)->first();
                $needStartNewRound = $game->need_start_new_round;

                print_r( " ДО:" . $remainingTimeOfRound .  "; ");
                print_r(' НУЖЕН=' . $needStartNewRound . "");

                if ($needStartNewRound == Game::YES) {

                    print_r(' ВСЕ ИГРОКИ СДЕЛАЛИ ХОД!!! ');
                    print_r(' НУЖЕН=' . $needStartNewRound);

                    // Добавить новый раунд.

                    break;
                }
            }
            
            if ( $currentTime > $roundEndTime) {

                // 1. Сделать ходы за игроков.
                // 2. Добавить новый раунд.
                print_r(' ВРЕМЯ РАУНДА ВЫШЛО!!! ');

                $this->makeMoveIsNeeded($this->game);

                break;
            }
           
           
            sleep(1);
        }


       
    }

    
    public function canStartNewRound(Game $game, $remainingTimeOfRound)
    {   
        
        
        // Получаем переменную флаг $game->needStartNewRound == true.
       


        // if ($remainingTimeOfRound == 0) {

        //     print_r('Время вышло!');
        //     $this->reasonEndRound = GameProcessJob::ROUND_TIME_IS_UP;

        //     return true;
        // }
    }


    public function StartNewRound(Game $game)
    {
        
        // 1. Создать новый раунд.
        $round = new Round();
        $round->game_id = $game->id;
        $round->number = $game->getLastFinishedRound()->number + 1; // 
        $round->save();

         // 2. Запустить событые начала нового раунда. Передать игру.
        //  GameNewRoundStartEvent::dispatch(GameResource::make($this->game));
         // 4. Вернуть ресурс нового созданного раунда.
        
    }


    public function makeMoveIsNeeded(Game $game)
    {
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;

        $players = [$firstPlayer, $secondPlayer];

        
        foreach ($players as $player) {

            $move = Move::where('game_id', $game->id)->where('player_id', $player->id )->first();

            if ($move instanceof Move) {

              return; 
            }

            $activeRound = $game->getActiveRound();

            $move = new Move();
            $move->game_id = $game->id;
            $move->round_number = $activeRound->number;
            $move->player_id = $player->id;
            $move->figure = Game::FIGURE_NONE;
            $move->save();
        }

        // Подсчитать победителя и завершить раунд. Переиспользовать метод finishRoundIfNeeded().
    }
}
