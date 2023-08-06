<?php

namespace App\Services;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Contains methods for updating user data and other business logic related to users.
 */
class UserService 
{   
    /**
     * UserService constructor.
     */
    public function __construct(private UserRepository $userRepository){}
    
    /**
     * Updates the user's game status to free.
     */
    public function makeUserFree($player): void
    {
        $player->game_status = User::FREE;
        $player->save();
    }

    /**
     * Updates the user's game status to waiting player.
     */
    public function putUserInStandbyMode($player): void
    {
        $player->game_status = User::WAITING_PLAYER;
        $player->save();
    }

    /**
     * Updates the list of users who are online.
     */
    public function updateUserList(): void
    {   
        $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4);

        if ($users->isNotEmpty()) {
            AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        }
    }
}