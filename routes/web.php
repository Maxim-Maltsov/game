<?php

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Controllers\GameController;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\Move;
use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;

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

    // $users = User::all();

    // foreach($users as $user) {

    //     print_r($user->id . "  " . " " . $user->canPlay() . "</br>");
    // }

    // exit();


    

    //////
    Cookie::forever('Round','fgfhgfhg');
    cookie('test', 'тестовое значение' , 500 );

    $test = Cookie::get('test');

    $tt = Cookie::has('Round');

    $cookie = Cookie::get('Round');

    Cookie::forget('Round');

    dd($test, $tt, $cookie);
    /////

    // $game = Game::where('id', 56)->first();

    // $time = $game->getRemainingTimeOfRound();
    // $need = $game->needStartNewRound;
    
    
    // $lastRound = $game->getLastRound();

    // $activeRound = $game->getActiveRound();
    // $remainingTime = $game->getRemainingTimeOfRound();

    // $gameResource = GameResource::make($game);
    

    // $firstPlayer = $game->firstPlayer;

    // $play = Game::showGameplayBlock();
    // $offer = Game::showOfferBlock();
    // $waiting = Game::showWaitingBlock();

    // $user = User::where('id', Auth::id())->first();
    // $can_play = $user->canPlay();

    // $token = session('API-Token');
    
    // dd($need,  $game, $lastRound,  $activeRound, $gameResource, $play, $offer, $waiting, $token, $can_play,  $firstPlayer);

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});