<?php

namespace App\Services;

use App\Models\User;

/**
 * Contains methods for updating user data and other business logic related to users.
 */
class UserService 
{
    /**
     * Update the user's game status to free.
     */
    public function makeUserFree($Player): void
    {
        $Player->game_status = User::FREE;
        $Player->save();
    }
}