<?php

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Controllers\GameController;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\Move;
use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
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

    $game = Game::where('id', 143)->first();

    // $lastRound = $game->getLastFinishedRound();

    $moves = $game->getMovesLastFinishedRound();

    $result = $game->getRoundResults();

    
   

    dd( $moves, $result );

    // $firstPlayer = $game->firstPlayer;
    // $secondPlayer = $game->secondPlayer;

    // $players = [$firstPlayer, $secondPlayer];
   
    // dd($firstPlayer, $secondPlayer, $players);

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});