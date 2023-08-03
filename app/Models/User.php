<?php

namespace App\Models;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Events\FirstPlayerCancelInviteEvent;
use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\FirstPlayerMadeMoveEvent;
use App\Events\GameStartEvent;
use App\Events\InviteToPlayEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Events\SecondPlayerMadeMoveEvent;
use App\Events\SecondPlayerRejectInviteEvent;
use App\Exceptions\MoveAlreadyMadeException;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Requests\GameRequest;
use App\Http\Requests\MoveRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\MoveResource;
use App\Http\Resources\UserCollection;
use App\Jobs\GameProcessJob;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use PhpParser\Node\Expr\Cast\Int_;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Online status.
    const OFFLINE = 0;
    const ONLINE = 1;


    // Game status.
    const GIVING_REPLY = 0;
    const WAITING_PLAYER = 0;
    const PLAYING = 1;
    const FREE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        
        'name',
        'email',
        'password',
        'online_status',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // Methods.
    
    
    public function canPlay(): bool
    {   
        $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where(function ($query) {
                        $query->where('player_1', $this->id);
                        $query->orWhere('player_2', $this->id);
                    })->first();
        
        if ($game instanceof Game) {

            return false;
        }

        return true;
    }

    /**
     * Updates the user's status to "offline".
     */
    public function makeUserStatusOffline(): void
    {
        $user =  User::where('id', $this->id)->first();
    
        $user->online_status = User::OFFLINE;
        $user->save();

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
    }


    public static function invite(int $secondPlayerId): GameResource  
    {
        $firstPlayer = User::where('id', Auth::id())->first();
        $secondPlayer = User::where('id', $secondPlayerId)->first();

        if ($firstPlayer == null || $secondPlayer == null)
        {   
            throw new PlayerNotFoundException('Player not found.');
        }
        
        if ($firstPlayer->id == $secondPlayer->id) {

            throw new YouCannotInviteYourselfException('You cannot invite yourself.');
        } 

        $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where('player_1', $firstPlayer->id)
                    ->first();
        
        if ($game instanceof Game) {

            throw new YouСannotOfferTwoGamesAtOnceException('You have already offered to play to another player. Wait for a response or cancel the game with ' . $game->secondPlayer->name . '.');
        }

        $game = Game::whereIn('status', [Game::WAITING_PLAYER, Game::IN_PROCESS])
                    ->where('player_2', $firstPlayer->id)
                    ->first();

        if ($game instanceof Game) {

            throw new YouСannotAgreeTwoGamesAtOnceException('You have already been offered to play. Accept the offer or refuse the offer. ' . 'Offer from '. $game->firstPlayer->name . '.');
        }

        $firstPlayer->game_status = User::WAITING_PLAYER;
        $firstPlayer->save();

        $secondPlayer->game_status = User::GIVING_REPLY;
        $secondPlayer->save();
        
        $attributes = array('player_2' => $secondPlayerId);

        $game = new Game($attributes);
        $game->player_1 = $firstPlayer->id;
        $game->status = Game::WAITING_PLAYER;
        $game->save();

        InviteToPlayEvent::dispatch( GameResource::make($game));

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.
        
        return GameResource::make($game);
    }
    
    
    public static function play(Game $game): GameResource
    {
        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::PLAYING;
        $firstPlayer->save();

        $secondPlayer = $game->secondPlayer;
        $secondPlayer->game_status = User::PLAYING;
        $secondPlayer->save();

        $game->status = Game::IN_PROCESS;
        $game->start = Carbon::now();
        $game->save();

        $round = new Round();
        $round->game_id = $game->id;
        $round->number = 1;
        $round->save();

        GameProcessJob::dispatch($game);
        GameStartEvent::dispatch(GameResource::make($game));
        
        return GameResource::make($game);
    }


    // public static function reject(Game $game): bool
    // {   
    //     $firstPlayer = $game->firstPlayer;
    //     $firstPlayer->game_status = User::FREE;
    //     $firstPlayer->save();

    //     $game->delete();
        
    //     SecondPlayerRejectInviteEvent::dispatch(GameResource::make($game));
        
    //     // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.

    //     return true;
    // }
    
    
    public static function leave(Game $game): GameResource
    {   
        $firstPlayer = $game->firstPlayer;
        $firstPlayer->game_status = User::FREE;
        $firstPlayer->save();

        $secondPlayer = $game->secondPlayer;
        $secondPlayer->game_status = User::FREE;
        $secondPlayer->save();

        $leaving_player = Auth::id();
        $winned_player = ($leaving_player == $game->player_1)? $game->player_2 : $game->player_1;
        
        $activeRound = $game->getActiveRound();
        $activeRound->status = Round::FINISHED;
        $activeRound->winned_player = $winned_player;
        $activeRound->save();

        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->winned_player = $winned_player;
        $game->leaving_player = $leaving_player;
        $game->need_start_new_round = Game::NO;
        $game->save();

        FirstPlayerLeavedGameEvent::dispatch(GameResource::make($game));
        SecondPlayerLeavedGameEvent::dispatch(GameResource::make($game));

        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.

        return GameResource::make($game);
    }

    
    public static function move(int $gameId, int $roundNumber, int $figure): MoveResource
    {   
        $player = Auth::user();
        
        $move = Move::where('game_id', $gameId)
                    ->where('player_id', $player->id )
                    ->where('round_number', $roundNumber)
                    ->first();

        if ($move instanceof Move) {

          throw new MoveAlreadyMadeException('You have already made a move in this round.');
        }

        $attributes = array(     
            'game_id' =>   $gameId,
            'round_number' => $roundNumber,
            'figure' => $figure,
        );

        $move = new Move($attributes);
        $move->player_id = $player->id;
        $move->save();

        $game = Game::where('id', $gameId)->first();
        
        FirstPlayerMadeMoveEvent::dispatch(GameResource::make($game));
        SecondPlayerMadeMoveEvent::dispatch(GameResource::make($game));

        $game->finishRoundIfNeeded();

        return MoveResource::make($move);
    }
}
