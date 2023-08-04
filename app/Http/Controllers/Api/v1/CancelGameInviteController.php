<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\CancelGameInviteAction;
use App\Models\Game;
use App\Services\UserService;
use Illuminate\Http\Response;

/**
 * Handles a request to cancel the invitation to the game.
 */
class CancelGameInviteController 
{
    public function __invoke(Game $game, CancelGameInviteAction $cancelGameInvite, UserService $userService): Response
    {
        $cancelGameInvite->handle($game);
        $userService->updateUserList();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}