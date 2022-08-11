<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\GameNotFoundException;
use App\Exceptions\MoveAlreadyMadeException;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Requests\MoveRequest;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{

    public function inviteToPlay(GameRequest $request)
    {   

        try {

            return User::invite($request);
        }
        catch (PlayerNotFoundException $e) {

            Log::info($e->getMessage());

            return response()->json([ 'data' => [
                
                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch (YouCannotInviteYourselfException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch (YouСannotOfferTwoGamesAtOnceException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch (YouСannotAgreeTwoGamesAtOnceException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
    }


    public function cancelInvite(Game $game)
    {  
        return User::cancel($game);
    }


    public function acceptInvite(Game $game) {

       return User::play($game);
    }
    

    public function rejectInvite(Game $game)
    {
       return User::reject($game);
    }


     public function leaveGame(Game $game)
    {
       return User::leave($game);
    }


    public static function makeMove(MoveRequest $request)
    {  
        try {

            return User::move($request);
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
