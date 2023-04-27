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
use Illuminate\Http\Response as HttpResponse;

class GameController extends Controller
{

    public function inviteToPlay(GameRequest $request)
    {   
        try {

            $secondPlayerId = $request->player_2;
            return User::invite($secondPlayerId);
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
       if (User::cancel($game)) {
            return response(null, HttpResponse::HTTP_NO_CONTENT);
       }
    }
    
    
    public function acceptInvite(Game $game) 
    {
       return User::play($game);
    }
    
    
    public function rejectInvite(Game $game) 
    {
        if (User::reject($game)) {
            return response(null, HttpResponse::HTTP_NO_CONTENT);
        }
    }
    

    public function leaveGame(Game $game)
    {
       return User::leave($game);
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
