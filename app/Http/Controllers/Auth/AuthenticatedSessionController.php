<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ApiTokenServisece;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\ApiAuthenticateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthenticatedSessionController extends Controller
{
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
        // Sunctum. Autorization token.
        ApiAuthenticateService::deleteToken(Auth::user());
        
        // Обновляет Онлайн-Статус и Вызвает событие AmountUsersOnlineChangedEven.
        User::makeUserStatusOffline(Auth::id());


        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
