<?php

namespace App\Http\Controllers\Auth;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repositories\UserRepository;
use App\Services\ApiAuthenticateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthenticatedSessionController extends Controller
{   

    public function __construct(private UserRepository $userRepository) {}

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store (LoginRequest $request) 
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Sunctum. Autorization token.
        ApiAuthenticateService::makeToken(Auth::user());
       
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {   
        // Updates the user's status to "offline".
        $user = $request->user();
        $user->makeUserStatusOffline();

        // Sunctum. Destroy the authorization token.
        ApiAuthenticateService::deleteToken($user);
        
        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
        $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
