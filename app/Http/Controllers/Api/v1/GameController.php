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

        $game = new Game();
        $game->player_1 = Auth::id();
        $game->player_2 = $request->player_2;
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
        //
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
