<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\InviteToPlayAction;
use App\Exceptions\PlayerNotFoundException;
use App\Http\Requests\GameRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Handles a request to invite a second player to take part in the game.
 */
class InviteToPlayController 
{
    public function __invoke(GameRequest $request, InviteToPlayAction $inviteToPlay, UserService $userService)
    {   
        try {

            $game = $inviteToPlay->handle($request->player_2);
            $userService->updateUserList();
           
            return $game;
        }
        catch (Exception $e) {

            return response()->json([ 'data' => [
                
                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
    }
}