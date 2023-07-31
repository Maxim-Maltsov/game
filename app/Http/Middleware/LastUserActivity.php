<?php

namespace App\Http\Middleware;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LastUserActivity
{    
    /**
     *  LastUserActivity middleware constructor.
     */
    public function __construct(private UserRepository $userRepository) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }
        
        // Updating the list of users with the "online" status. 
        $request->user()->update(['last_activity' => DB::raw('NOW()'), 'online_status' => User::ONLINE]);

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
        $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4);
        
        if ($users->isNotEmpty()) {
            AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        }

        return $next($request);
    }
}
