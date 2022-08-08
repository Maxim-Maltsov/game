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

    

    $game = Game::where('id', 9)->first();


    $rounds = DB::table('rounds')
                ->where('rounds.game_id', $game->id)
                ->leftJoin('moves', 'moves.game_id', '=', 'rounds.game_id')
                ->select( 'rounds.game_id', 'rounds.number as round_number', 'rounds.winned_player', 'rounds.draw', 'moves.player_id', 'moves.figure', 'rounds.created_at')
                ->groupBy('rounds.number', 'rounds.game_id', 'rounds.winned_player', 'rounds.draw', 'moves.player_id', 'moves.figure', 'rounds.created_at')
                ->get();

    
    $rounds->transform(function ($round) use ($game) {
                
                $round->game_id = $game->id;

                if ($round->player_id === $game->player_1) {

                    $round->move_player_1 = $round->figure;

                } else {

                    $round->move_player_2 = $round->figure;
                }
                    
                return $round;
            });


    $roundsCollection = collect();
        
    $new_round = [];

    // $roundsCollection->dump();
    
        foreach ($rounds as $round) {
           
                                             echo "$round->round_number </br>";

            $new_round['game_id'] = $round->game_id;
            $new_round['round_number'] = $round->round_number;
            $new_round['winned_player'] = $round->winned_player;
            $new_round['draw'] = $round->draw;
            $new_round['created_at'] = $round->created_at;

            if ($round->player_id == $game->player_1  && $new_round['round_number'] == $round->round_number ) {
                
                $new_round['move_player_1'] = $round->figure; 
            } 

            if ($round->player_id == $game->player_2  && $new_round['round_number'] == $round->round_number ) {
                
                $new_round['move_player_2'] = $round->figure;
            }

            $roundsCollection->push($new_round);

            // $roundsCollection->dump();
            // var_dump($new_round);
        }


    $chunk = $roundsCollection->splice(1);
    $chunk->all();

    $unique = $chunk->unique('round_number');
    $unique->values()->all();
   
    
    dd( "Информация о раундах игры. Собрана из двух таблиц:", $rounds,  "Новый собранный раунд:", $new_round, "Коллекция из собранных раундов:",  $roundsCollection, "Вывод только уникальных значений с сортировкой по номеру раунда:", $unique,);

    
    
    // $activeRound = $game->getActiveRound();

    // $currentTime = Carbon::now();
    // $roundStartTime = $activeRound->created_at;
    // $roundEndTime = $roundStartTime->copy()->addSeconds(env('ROUND_TIME'));

    // // $remainingTime = $currentTime->diffInSeconds($roundEndTime, false);
    // $remainingTime = ($currentTime->diffInSeconds($roundEndTime, false) >= 0 )? $currentTime->diffInSeconds($roundEndTime, false) : 0;

    // $mothod = $game->getRemainingTimeOfRound();

    // dd("Номер раунда:", $activeRound->number, "Время полученное через метод:", $mothod, "Оставшееся время:", $remainingTime, "Текущее время:", $currentTime , "Время окончания раунда:", $roundEndTime,"Время начала раунда:", $roundStartTime,  );

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';



Route::middleware(['auth', 'last-activity'])->group(function () {

    Route::get('/', [GameController::class, 'index'])->name('home');
});