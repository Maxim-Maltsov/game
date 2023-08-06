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
     * CancelGameAction constructor.
     */
    public function __construct(private UserService $userService) {}

    /**
    * Triggers the action to reject the invitation to the game.
    */
    public function handle(Game $game): void
    {   
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
        
        $this->userService->makeUserFree($firstPlayer);
        $this->userService->makeUserFree($secondPlayer);

        $game->delete();
        
        SecondPlayerRejectInviteEvent::dispatch(GameResource::make($game));
    }
}