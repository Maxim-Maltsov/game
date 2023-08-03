<?php

namespace App\Actions;

use App\Events\SecondPlayerRejectInviteEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;

/**
 * Rejects the invitation to the game.
 */
class RejectGameInviteAction
{   
    /**
    * Triggers the action to reject the invitation to the game.
    */
    public function handle(Game $game)
    {   
        // Перенести логику обновления "игрового статуса" 1-го игрока в класс "UserService".
        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::FREE;
        $firstPlayer->save();

        $game->delete();
        
        SecondPlayerRejectInviteEvent::dispatch(GameResource::make($game));
    }
}