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
     * CancelGameAction constructor.
     */
    public function __construct(private UserService $userService) {}
    
    /**
    * Triggers the action to cancel the invitation to the game.
    */
    public function handle(Game $game): void
    {   
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
        
        $this->userService->makeUserFree($firstPlayer);
        $this->userService->makeUserFree($secondPlayer);

        $game->delete();
        
        FirstPlayerCancelInviteEvent::dispatch(GameResource::make($game));
    }
}