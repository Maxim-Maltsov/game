<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Exceptions\GameNotFoundException;
use App\Exceptions\MoveAlreadyMadeException;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\UserCollection;
use App\Models\Game;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response as HttpResponse;

class GameController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    
    public function acceptInvite(Game $game) 
    {
       return User::play($game);
    }
    
    
    public static function makeMove(MoveRequest $request) 
    {
        try {
            
            $gameId = $request->game_id;
            $roundNumber = $request->round_number;
            $figure = $request->figure;

            return User::move($gameId, $roundNumber, $figure);
        }
        catch (MoveAlreadyMadeException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }   
    }
    
    
    public function initGame()
    {   
        try {

            return Game::init();
        }
        catch (GameNotFoundException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
    }

}
