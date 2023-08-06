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


    public function inviteToPlay(GameRequest $request)
    {   
        try {

            $secondPlayerId = $request->player_2;
            $game = User::invite($secondPlayerId);
            
            // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
            $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4); 
            AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
           
            return $game;
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
