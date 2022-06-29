<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\InviteToPlayEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{

    public function inviteToPlay(GameRequest $request)
    {   
        $player_1 = Auth::user();
        $player_2 = User::where('id', $request->player_2)->first();


        if ($player_1 === null) {

            return response()->json([ 'data' => [

                'error' => "User not found.",
            ]]);

        }

        if ($player_2 === null) {

            return response()->json([ 'data' => [

                'error' => "User Two not found.",
            ]]);
        }

        if ($player_1->id == $player_2->id) {

            return response()->json([ 'data' => [

                'error' => "You can't invite yourself",
            ]]);
        }
        

        $game = Game::where('player_1', $player_1->id)->first();
        
        if ($game instanceof Game) {

            return response()->json([ 'data' => [

                'error' => "You're already playing " . $player_2->name ,
            ]]);
        }

        // $game = Game::whereIn('player_2', [$player_1->id, $player_2->id])->first();
        
        // if ($game instanceof Game) {

        //     return response()->json([ 'data' => [

        //         'error' => "You're already playing ",
        //     ]]);
        // }

        $game = Game::whereIn('player_1', [$player_1->id, $player_2->id])
                    ->whereIn('player_2', [$player_2->id, $player_1->id])
                    ->whereIn('status', [Game::IN_PROCESS, Game::WAITING_PLAYER])
                    ->first();

        
        if ($game instanceof Game) {

            if ($game->status == Game::WAITING_PLAYER) {

                return response()->json([ 'data' => [

                    'error' => "Waiting for start game with " . $player_2->name ,
                ]]);
             }

             if ($game->status == Game::IN_PROCESS) {

                return response()->json([ 'data' => [

                    'error' => "The game in process" ,
                ]]); 
             }
        }
    

        $game = new Game($request->validated());  // $request->validated(), присваивает значение - $game->player_2 = $request->player_2;
        $game->player_1 = Auth::id();
        $game->status = Game::WAITING_PLAYER;
        $game->save();

        // InviteToPlayEvent::dispatch($player_1, $player_2, $game);

        return new GameResource($game);
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
        // $player_1 = Auth::user();
        // $player_2 = User::where('id', $request->id)->first();

        // $game = new Game();
        // $game->player_1 = Auth::id();
        // $game->player_2 = $request->id;
        // $game->status = Game::WAITING_PLAYER;
        // $game->save();

        // InviteToPlayEvent::dispatch($player_1, $player_2, $game);

        // return new GameResource($game);
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
    public function destroy($id)
    {
        //
    }
}
