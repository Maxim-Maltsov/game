<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\FirstPlayerGameDeleteEvent;
use App\Events\InviteToPlayEvent;
use App\Events\SecondPlayerGameDeleteEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{

    public function inviteToPlay(GameRequest $request)
    {   
        $player_1 = Auth::user();
        $player_2 = User::where('id', $request->player_2)->first();


        if ($player_1->id == $player_2->id) {

            return response()->json([ 'data' => [

                'message' => "You can't invite yourself",
                'exception' => true,
            ]]);
        }
        

        $game = Game::where('player_1',  $player_1->id)->whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])->first();
        
        if ($game instanceof Game) {

            return response()->json([ 'data' => [

                'message' => "You have already offered to play to another player. Wait for a response or cancel the game with " . $game->secondPlayer->name . ".",
                'exception' => true,
            ]]);
        }


        $game = Game::where('player_2', $player_1->id)->whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])->first();

        if ($game instanceof Game) {

            return response()->json([ 'data' => [

                
                'message' => "You have already been offered to play. Accept the offer or refuse the offer. " . "Offer from ". $game->firstPlayer->name . ".",
                'exception' => true,
            ]]);
        }
          

        $game = new Game($request->validated()); 
        $game->player_1 = Auth::id();
        $game->status = Game::WAITING_PLAYER;
        $game->save();

        InviteToPlayEvent::dispatch( GameResource::make($game));

        return new GameResource($game);
    }


    public function acceptInvite($id) {

        //
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
    public function show($id)
    {
        return new GameResource(Game::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
        $game->delete();

        FirstPlayerGameDeleteEvent::dispatch(GameResource::make($game));
        SecondPlayerGameDeleteEvent::dispatch(GameResource::make($game));

        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }

}
