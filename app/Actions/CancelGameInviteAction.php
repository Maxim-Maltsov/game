<?php 

namespace App\Actions;

use App\Events\FirstPlayerCancelInviteEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;

/**
 * Cancels the invitation to the game.
 */
class CancelGameInviteAction
{   
    /**
    * Triggers the action to cancel the invitation to the game.
    */
    public function handle(Game $game)
    {   
        // Перенести логику обновления "игрового статуса" 1-го игрока в класс "UserService".
        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::FREE;
        $firstPlayer->save();

        $game->delete();
        
        FirstPlayerCancelInviteEvent::dispatch(GameResource::make($game));
    }
}