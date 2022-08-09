<?php

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Controllers\GameController;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\HistoryResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\History;
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

    

    $game = Game::where('id', 35)->first();

    

    $historyLastRound = $game->getHistoryLastRound();
    
    $history = $game->getHistoryGame();

    dd( $historyLastRound, $history);


    // Получаем последний раунд.
    // $round = DB::table('rounds')
    //             ->where('rounds.game_id', $game->id )
    //             ->select( 'rounds.game_id', 'rounds.number as round_number', 'rounds.winned_player', 'rounds.draw', 'rounds.created_at')
    //             ->selectRaw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=? LIMIT 1) as move_player_1', [ $game->player_1])
    //             ->selectRaw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=? LIMIT 1) as move_player_2', [ $game->player_2])
    //             ->orderByDesc('round_number')
    //             ->limit(1)
    //             ->get();
    
    // dd($round);


    // Первый вариант c selectRaw().
    // $rounds = DB::table('rounds')
    //             ->where('rounds.game_id', $game->id )
    //             ->select( 'rounds.game_id', 'rounds.number as round_number', 'rounds.winned_player', 'rounds.draw', 'rounds.created_at')
    //             ->selectRaw('( SELECT moves.figure FROM moves where moves.round_number = rounds.number AND moves.player_id = ? LIMIT 1) as move_player_1', [ $game->player_1])
    //             ->selectRaw('( SELECT moves.figure FROM moves where moves.round_number = rounds.number AND moves.player_id = ? LIMIT 1) as move_player_2', [ $game->player_2])
    //             ->get();

    // dd($rounds);


    // Первый вариант c setBindings().
    // $rounds = DB::table('rounds')
    //             ->where('rounds.game_id', $game->id )
    //             ->select( 
    //                 'rounds.game_id', 'rounds.number as round_number', 'rounds.winned_player', 'rounds.draw', 'rounds.created_at' ,
    //                 DB::raw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=? LIMIT 1) as move_player_1'),
    //                 DB::raw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=? LIMIT 1) as move_player_2')
    //             )
    //             ->setBindings([ $game->player_1,  $game->player_2, $game->id])
    //             ->get();

    // dd($rounds);

    
    // dd($rounds);

    // Первый вариант с join.
    // $rounds = DB::table('rounds')
    //             ->where('rounds.game_id', $game->id)
    //             ->select( 
    //                 'rounds.game_id', 'rounds.number as round_number', 'rounds.winned_player', 'rounds.draw',  'rounds.created_at' ,
    //                 DB::raw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=games.player_1) as move_player_1'),
    //                 DB::raw('(SELECT moves.figure FROM moves where moves.round_number=rounds.number AND moves.player_id=games.player_2) as move_player_2')
    //             )
    //             ->join('games', 'games.id', '=', 'rounds.game_id')
    //             ->get();  
    
    // dd($rounds);




    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});