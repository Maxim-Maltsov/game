<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\RejectGameInviteAction;
use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\Game;
use App\Repositories\UserRepository;
use Illuminate\Http\Response;

/**
 * Processes the request to reject the invitation to the game.
 */
class RejectGameInviteController 
{
    public function __invoke(Game $game, RejectGameInviteAction $rejectGameInvite, UserRepository $userRepository)
    {
        $rejectGameInvite->handle($game);

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
        $users = $userRepository->getEveryoneWhoOnlineWithPaginated(4); 
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));

        return response(null, Response::HTTP_NO_CONTENT);
    }
}