<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\LeaveGameAction;
use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use App\Models\Game;
use App\Repositories\UserRepository;

/**
 * Handles a request to leave the game before it ends.
 */
class LeaveGameController 
{
    public function __invoke(Game $game, LeaveGameAction $leaveGame, UserRepository $userRepository) :GameResource
    {
        $leaveGame->handle($game);

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
        $users = $userRepository->getEveryoneWhoOnlineWithPaginated(4); 
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));

        return GameResource::make($game);
        
    }
}