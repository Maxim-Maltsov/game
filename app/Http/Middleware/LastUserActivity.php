<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Updates the user's last activity time and changes the user's online status to "online" in the time the request is made.
 */
class LastUserActivity
{    
    /**
     *  LastUserActivity middleware constructor.
     */
    public function __construct(private UserService $userService) {}

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
        
        $request->user()->update(['last_activity' => DB::raw('NOW()'), 'online_status' => User::ONLINE]);
        
        $this->userService->updateUserList();

        return $next($request);
    }
}
