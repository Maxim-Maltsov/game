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

    $game = Game::where('id', 150)->first();

    // $lastRound = $game->getLastFinishedRound();
    
    // $rounds = $game->rounds;
    // $moves = $game->moves;
    // $movesLastRound = $game->getMovesLastFinishedRound();

    // $result = $game->getRoundResults();

    // dd( $rounds, $moves, $movesLastRound, $result );


    $activeRound = $game->getActiveRound();
    $request = new MoveRequest(['round_number' => $activeRound->number]);
    $moves = $game->getMovesOfActiveRound($request['round_number']);

    $game->player_1;

    $moves[0]->player_id;

    $roundMoves = $moves->all();
    $roundMoves[0];

    $move_player_1 = ($game->player_1 == $moves[0]->player_id)? $moves[0]->figure : $moves[1]->figure;
    $move_player_2 = ($game->player_2 == $moves[0]->player_id)? $moves[0]->figure : $moves[1]->figure;

    dd($roundMoves[0], $moves, $game->player_1, $moves[0]->player_id, "Ход первого игрока $move_player_1", "Ход второго игрока $move_player_2");

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