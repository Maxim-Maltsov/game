<?php

namespace App\Actions;

use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
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
        // Перенести логику обновления "игрового статуса" 1-го игрока в класс "UserService".
        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::FREE;
        $firstPlayer->save();

        // Перенести логику обновления "игрового статуса" 2-го игрока в класс "UserService".
        $secondPlayer = $game->secondPlayer;
        $secondPlayer->game_status = User::FREE;
        $secondPlayer->save();

        // Перенести логику определения победителя в игре при досрочном её завершении в класс "GameService".
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