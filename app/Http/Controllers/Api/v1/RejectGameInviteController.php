<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\RejectGameInviteAction;
use App\Models\Game;
use App\Services\UserService;
use Illuminate\Http\Response;

/**
 * Handles a request to reject the invitation to the game.
 */
class RejectGameInviteController
{
    public function __invoke(Game $game, RejectGameInviteAction $rejectGameInvite, UserService $userService): Response
    {
        $rejectGameInvite->handle($game);
        $userService->updateUserList();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}