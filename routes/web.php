<?php

use App\Http\Controllers\GameController;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/welcome', function () {

    $user =  User::where('id', Auth::id())->first();
    $userResource = UserResource::make($user);



    $play = Game::showGameplayBlock();
    $offer = Game::showOfferBlock();
    $waiting = Game::showWaitingBlock();
    $can_play = User::canPlay($user->id);

    $token = session('API-Token');
    dd($play, $offer, $waiting, $token, $can_play, $userResource);

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});