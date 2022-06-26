<?php

namespace App\Http\Middleware;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LastUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
 
            $expireTime = Carbon::now()->addMinute();
            Cache::put('online'.Auth::id(), true, $expireTime);

            User::where('id', Auth::id())->update(['last_activity' => DB::raw('NOW()'), 'online_status' => User::ONLINE]);

            $users = User::getOnlineUsersPaginate(4);

            if ($users->count() > 0) { 

                AmountUsersOnlineChangedEvent::dispatch(new UserCollection($users));
            }
        }

        return $next($request);
    }
}
