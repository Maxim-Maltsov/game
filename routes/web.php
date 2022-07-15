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

    // $users = User::all();

    // foreach($users as $user) {

    //     print_r($user->id . "  " . " " . $user->canPlay() . "</br>");
    // }

    // exit();

    $game = Game::where('id', 1)->first();
    $round = $game->rounds()->where('status', Round::NO_FINISHED)->first();
    

    $game->getRemainingTimeOfRound();
    // $last = $game->last_round_start->addSeconds(env('ROUND_TIME'));

    $gameResurce = GameResource::make($game);

    

    $firstPlayer = $game->firstPlayer;

    $play = Game::showGameplayBlock();
    $offer = Game::showOfferBlock();
    $waiting = Game::showWaitingBlock();

    $user = User::where('id', Auth::id())->first();
    $can_play = $user->canPlay();

    $token = session('API-Token');
    
    dd( $game, $round, $gameResurce, $play, $offer, $waiting, $token, $can_play,  $firstPlayer);

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});