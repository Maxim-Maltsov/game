<?php

namespace App\Actions;

use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * The player leaves the game before it ends.
 */
class LeaveGameAction 
{
     /**
    * Triggers the action that allows you to leave the game before it ends.
    */
    public function handle(Game $game)
    {   
        $userService = new UserService();

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
       
        $userService->makeUserFree($firstPlayer);
        $userService->makeUserFree($secondPlayer);

        // Перенести логику определения победителя в игре при досрочном её завершении в класс "RefereeService".
        $leaving_player = Auth::id();
        $winned_player = ($leaving_player == $game->player_1)? $game->player_2 : $game->player_1;
        
        // Перенести метод получения активного раунда в класс "RoundRepository".
        $activeRound = $game->getActiveRound();
       
        // Перенести метод обновления данных активного раунда в класс "RoundService".
        $activeRound->status = Round::FINISHED;
        $activeRound->winned_player = $winned_player;
        $activeRound->save();

        // Перенести логику обновления "статуса игры" и других её данных в класс "GameService".
        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->winned_player = $winned_player;
        $game->leaving_player = $leaving_player;
        $game->need_start_new_round = Game::NO;
        $game->save();

        FirstPlayerLeavedGameEvent::dispatch(GameResource::make($game));
        SecondPlayerLeavedGameEvent::dispatch(GameResource::make($game));
    }
}