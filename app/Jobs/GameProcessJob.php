<?php

namespace App\Jobs;

use App\Events\GameNewRoundStartEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cookie;

class GameProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $game;

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
        $remainingTimeOfRound = $this->game->getRemainingTimeOfRound();

        while (true) {

            if ($remainingTimeOfRound > 0) {

                print_r($remainingTimeOfRound);

                // if ($this->canStartNewRound()) {

                //     $this->StartNewRound($this->game);
                // }
            }
        
            sleep(1);
        }


       
    }

    
    public function canStartNewRound()
    {   
        $cookie = Cookie::get('needStartNewRound'); // Получение $cookie.
        
        // Cookie::has('needStartNewRound'); // Проверяем есть ли $cookie c еаким ключём.

        if (Cookie::has('needStartNewRound')) {

            return true;
        }

        // if ($this->game->getRemainingTimeOfRound() == 0) {

            // Создать константу обозначающую это условие.

        //     return true;
        // }
    }


    public function StartNewRound(Game $game)
    {
        // 1. Сделать переменную $this->game->needStartNewRound = false;
        //////
        // $game->needStartNewRound = false;
        $cookie = Cookie::forget('needStartNewRound'); // удаление $cookie.
        //////

        // 2. Создать новый раунд.
        $round = new Round();
        $round->game_id = $game->id;
        $round->number = $game->getLastFinishedRound()->number + 1; // 
        $round->save();

         // 3. Запустить событые начала нового раунда. Передать игру.
        //  GameNewRoundStartEvent::dispatch(GameResource::make($this->game));
         // 4. Вернуть ресурс нового созданного раунда.
        
    }


    public function makeMoveIsNeeded(Game $game)
    {


        // Сделать ходы игроков. Использовать
        // $plyers = // Получить всех игроков участвующих в игре или их id.

        $firstPlayer = $game->firstPlayer();
        $secondPlayer = $game->secondPlayer();

        $players = [$firstPlayer, $secondPlayer];

        /* 
         Перебрать игроков циклом, делая ход за каждого игрока фигурой со значение NONE.
         Записываем ход в базу.

         Также сделать перед этим проверку на то, что игрок уже сделал ход.
        */
        
        foreach ($players as $player) {

            // 1. Cделать перед этим проверку на то, что игрок уже сделал ход.

            // 2. Cделать ход фигурой со значение NONE.
        }


        // $move = Move::where('game_id', $game->id)->where('player_id', $player->id )->first();

        // if ($move instanceof Move) {

        //   throw new MoveAlreadyMadeException('You have already made a move in this round.');
        // }

        // $move = new Move($request->validated()); // $request->validated() - saving in table moves 'game_id', 'round_number' and 'figure'.
        // $move->player_id = $player->id;
        // $move->save();

        // $game = Game::where('id', $request->game_id)->first();
        
        // FirstPlayerMadeMoveEvent::dispatch(GameResource::make($game));
        // SecondPlayerMadeMoveEvent::dispatch(GameResource::make($game));

        // $game->finishRoundIfNeeded($request);

        // return MoveResource::make($move);
    }
}
