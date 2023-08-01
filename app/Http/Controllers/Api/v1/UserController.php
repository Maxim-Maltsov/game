<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserCollection;
use App\Repositories\UserRepository;

/**
 * User management.
 * 
 * @package  App\Http\Controllers\Api\v1
 */
class UserController extends ApiController
{
    
    public function __construct(private UserRepository $userRepository)
    {   
        parent::__construct();
    }

    /**
     * Passes a list of all users who are "online"  to the client side for further rendering. 
     */
    public function index() :UserCollection
    {  
        $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4);

        return new UserCollection($users);
    }
}
