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
    public function makeUserFree($Player): void
    {
        $Player->game_status = User::FREE;
        $Player->save();
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