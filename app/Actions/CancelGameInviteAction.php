<?php 

namespace App\Actions;

use App\Events\FirstPlayerCancelInviteEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\UserService;

/**
 * Cancels the invitation to the game.
 */
class CancelGameInviteAction
{   
    /**
    * Triggers the action to cancel the invitation to the game.
    */
    public function handle(Game $game): void
    {   
        $userService = new UserService();

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
        
        $userService->makeUserFree($firstPlayer);
        $userService->makeUserFree($secondPlayer);

        $game->delete();
        
        FirstPlayerCancelInviteEvent::dispatch(GameResource::make($game));
    }
}