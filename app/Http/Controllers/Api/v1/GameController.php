<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Events\InviteToPlayEvent;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use App\Models\Game;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{

    public function inviteToPlay(GameRequest $request)
    {   

        try {

            return Game::invite($request);
        }
        catch( PlayerNotFoundException $e) {

            Log::info($e->getMessage());

            return response()->json([ 'data' => [
                
                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch( YouCannotInviteYourselfException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch( YouСannotOfferTwoGamesAtOnceException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }
        catch( YouСannotAgreeTwoGamesAtOnceException $e) {

            return response()->json([ 'data' => [

                'message' => $e->getMessage(),
                'exception' => true,
            ]]);
        }

        // $player_1 = Auth::user();
        // $player_2 = User::where('id', $request->player_2)->first();
        
        // if ($player_1->id == $player_2->id) {

        //     return response()->json([ 'data' => [

        //         'message' => "You can't invite yourself",
        //         'exception' => true,
        //     ]]);
        // }
        

        // $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
        //             ->where('player_1', $player_1->id)
        //             ->first();
        
        // if ($game instanceof Game) {

        //     return response()->json([ 'data' => [

        //         'message' => "You have already offered to play to another player. Wait for a response or cancel the game with " . $game->secondPlayer->name . ".",
        //         'exception' => true,
        //         'playing' => true,
        //     ]]);
        // }


        // $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
        //             ->where('player_2', $player_1->id)
        //             ->first();

        // if ($game instanceof Game) {

        //     return response()->json([ 'data' => [

        //         'message' => "You have already been offered to play. Accept the offer or refuse the offer. " . "Offer from ". $game->firstPlayer->name . ".",
        //         'exception' => true,
        //         'playing' => true,
        //     ]]);
        // }
          
        // $game = new Game($request->validated()); 
        // $game->player_1 = $player_1->id;
        // $game->status = Game::WAITING_PLAYER;
        // $game->save();

        // InviteToPlayEvent::dispatch( GameResource::make($game));

        // $users = User::getOnlineUsersPaginate(4);
        // AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        
        // return new GameResource($game);
    }


    public function cancelInvite(Game $game)
    {  
        return Game::cancel($game);
    }


    public function acceptInvite(Game $game) {

       return Game::play($game);
    }
    

    public function rejectInvite(Game $game)
    {
       return Game::reject($game);
    }


     public function leaveGame(Game $game)
    {
       return Game::leave($game);
    }


    public function initGame()
    {
        return Game::init();
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GameRequest $request)
    {   
       //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        return new GameResource($game);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Game $game)
    {
       //
    }

   
}
