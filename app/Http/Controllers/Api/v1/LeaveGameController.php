<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\LeaveGameAction;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\UserService;

/**
 * Handles a request to leave the game before it ends.
 */
class LeaveGameController 
{
    public function __invoke(Game $game, LeaveGameAction $leaveGame, UserService $userService): GameResource
    {
        $leaveGame->handle($game);
        $userService->updateUserList();
   
        return GameResource::make($game);
    }
}