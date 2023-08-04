<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\ApiAuthenticateService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{   

    public function __construct(private UserService $userService) {}

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

        // Sunctum. Revoke the authorization tokens.
        ApiAuthenticateService::deleteTokens($user);
        
        $this->userService->updateUserList();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
