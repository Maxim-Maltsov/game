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
        
        while (true) {
            
            $currentTime = Carbon::now()->secondsSinceMidnight();
            
            if ( $currentTime <= $roundEndTime) {

                if ($this->canStartNewRound($currentTime, $roundEndTime)) {

                    if ($this->endRoundReason == Game::ROUND_TIME_IS_UP) {

                        try {

                            $this->makeMoveIsNeeded($this->game);
                        }
                        catch (MoveAlreadyMadeException $e) {

                            // Log::info($e->getMessage());
                        } 
                    }
                    
                    $this->StartNewRound($this->game);

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

        // Разрешение на начало нового раунда, если истекло время предыдущего раунда.
        if ($currentTime >= $endTime) {
            
            $this->endRoundReason = Game::ROUND_TIME_IS_UP;
            return true;
        }
    }


    public function StartNewRound(Game $game)
    {       
        // 1. Создать новый раунд.
        $round = new Round();
        $round->game_id = $game->id;
        $round->number = $game->getLastFinishedRound()->number + 1; // 
        $round->save();

         // 2. Запустить событые начала нового раунда. Передать игру.
         GameNewRoundStartEvent::dispatch(GameResource::make($this->game));
         // 4. Вернуть ресурс нового созданного раунда.
    }


    public function makeMoveIsNeeded(Game $game)
    {
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;

        $players = [$firstPlayer, $secondPlayer];

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
