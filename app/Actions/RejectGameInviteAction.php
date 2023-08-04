<?php

namespace App\Actions;

use App\Events\SecondPlayerRejectInviteEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\UserService;

/**
 * Rejects the invitation to the game.
 */
class RejectGameInviteAction
{   
    /**
    * Triggers the action to reject the invitation to the game.
    */
    public function handle(Game $game): void
    {   
        $userService = new UserService;

        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
        
        $userService->makeUserFree($firstPlayer);
        $userService->makeUserFree($secondPlayer);

        $game->delete();
        
        SecondPlayerRejectInviteEvent::dispatch(GameResource::make($game));
    }
}