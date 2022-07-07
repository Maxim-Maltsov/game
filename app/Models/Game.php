<?php

namespace App\Models;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Events\FirstPlayerCancelInviteEvent;
use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\GameStartEvent;
use App\Events\InviteToPlayEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Events\SecondPlayerRejectInviteEvent;
use App\Exceptions\GameNotFoundException;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserCollection;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class Game extends Model
{
    use HasFactory;

     // Game status.

     const WAITING_PLAYER = 0;
     const IN_PROCESS  = 1;
     const FINISHED  = 2;

    // Game figure.

     const FIGURE_NONE = 0;
     const FIGURE_STONE = 1;
     const FIGURE_SCISSORS = 2;
     const FIGURE_PAPER = 3;
     const FIGURE_LIZARD = 4;
     const FIGURE_SPOCK = 5;
 

     protected $fillable = [ 'player_2'];

    // Relationship.

    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'player_1');
    }

    public function secondPlayer()
    {
        return $this->belongsTo(User::class, 'player_2');
    }

    public function winnedPlayer()
    {
        return $this->belongsTo(User::class, 'winned_player');
    }

    public function leavingPlayer()
    {
        return $this->belongsTo(User::class, 'leaving_player');
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }


    // Methods.

    public static function invite(GameRequest $request): GameResource
    {

        $player_1 = Auth::user();
        $player_2 = User::where('id', $request->player_2)->first();

        if ($player_1 == null || $player_2 == null)
        {   
            throw new PlayerNotFoundException('Player not found.');
        }
        
        if ($player_1->id == $player_2->id) {

            throw new YouCannotInviteYourselfException('You cannot invite yourself.');
        }
        

        $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where('player_1', $player_1->id)
                    ->first();
        
        if ($game instanceof Game) {

            throw new YouСannotOfferTwoGamesAtOnceException('You have already offered to play to another player. Wait for a response or cancel the game with ' . $game->secondPlayer->name . '.');
        }


        $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where('player_2', $player_1->id)
                    ->first();

        if ($game instanceof Game) {

            throw new YouСannotAgreeTwoGamesAtOnceException('You have already been offered to play. Accept the offer or refuse the offer. ' . 'Offer from '. $game->firstPlayer->name . '.');
        }
          
        $game = new Game($request->validated()); 
        $game->player_1 = $player_1->id;
        $game->status = Game::WAITING_PLAYER;
        $game->save();

        InviteToPlayEvent::dispatch( GameResource::make($game));

        $users = User::getOnlineUsersPaginate(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        
        return GameResource::make($game);
    }


    public static function cancel(Game $game): HttpResponse | ResponseFactory
    {
        $game->delete();
        
        FirstPlayerCancelInviteEvent::dispatch(GameResource::make($game));
        
        $users = User::getOnlineUsersPaginate(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        
        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }


    public static function play(Game $game): GameResource
    {
        $game->status = Game::IN_PROCESS;
        $game->start = Carbon::now();
        $game->save();

        GameStartEvent::dispatch(GameResource::make($game));

        return new GameResource($game);
    }


    public static function reject(Game $game): HttpResponse | ResponseFactory
    {
        $game->delete();
        
        SecondPlayerRejectInviteEvent::dispatch(GameResource::make($game));
        
        $users = User::getOnlineUsersPaginate(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));

        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }


    public static function leave(Game $game): GameResource
    {
        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->leaving_player = Auth::id();
        $game->save();



        FirstPlayerLeavedGameEvent::dispatch(GameResource::make($game));
        SecondPlayerLeavedGameEvent::dispatch(GameResource::make($game));

        $users = User::getOnlineUsersPaginate(4);
        AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));

        return GameResource::make($game);
    }


    public static function showWaitingBlock(): bool
    {
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_1', Auth::id())
                    ->first();
        
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function showOfferBlock(): bool
    {
        $game = Game::where('status', Game::WAITING_PLAYER)
                    ->where('player_2', Auth::id())
                    ->first();
       
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function showGameplayBlock(): bool
    {
        $game = Game::where('status', [Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->first();
                
        if ($game instanceof Game) {

            return true;
        }

        return false;
    }


    public static function init(): JsonResponse
    {   
        $game = Game::whereIn('status',[Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where(function ($query)  {
                        $query->where('player_1', Auth::id());
                        $query->orWhere('player_2', Auth::id());
                    })->first();
        

        if ( $game == null) {

            throw new GameNotFoundException('The Game Was Not Found! Start the game!');
        }
    
        return response()->json([ 'data' => [

            'game' => GameResource::make($game),
            'waiting' => Game::showWaitingBlock(),
            'offer' => Game::showOfferBlock(),
            'play' => Game::showGameplayBlock(),
            'leave' => Game::showGameplayBlock(),
        ]]);
    }

    
}
