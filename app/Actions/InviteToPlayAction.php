<?php 

namespace App\Actions;

use App\Events\InviteToPlayEvent;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\YouCannotInviteYourselfException;
use App\Exceptions\YouСannotAgreeTwoGamesAtOnceException;
use App\Exceptions\YouСannotOfferTwoGamesAtOnceException;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Repositories\GameRepository;
use App\Repositories\UserRepository;
use App\Services\GameService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

/**
 * Sends an invitation to the second player to take part in the game.
 */
class InviteToPlayAction
{   
     /**
     * InviteToPlayAction constructor.
     */
    public function __construct( private UserRepository $userRepository,
                                 private UserService $userService,
                                 private GameRepository $gameRepository,
                                 private GameService $gameService) {}
    
    /**
    * Triggers the action to invitation to the game.
    */
    public function handle(int $secondPlayerId): Game
    {   
        $firstPlayer = $this->userRepository->getUserById(Auth::id());
        $secondPlayer = $this->userRepository->getUserById($secondPlayerId);
       
        if (!$firstPlayer || !$secondPlayer) {  
            throw new PlayerNotFoundException('Player not found.');
        }
        
        if ($firstPlayer->id === $secondPlayer->id) {
            throw new YouCannotInviteYourselfException('You cannot invite yourself.');
        } 

        $game = $this->gameRepository->getGameWhereUserIsFirstPlayer($firstPlayer->id);
        
        if ($game) {
            throw new YouСannotOfferTwoGamesAtOnceException
            ('You have already offered to play to another player. Wait for a response or cancel the game with ' . $game->secondPlayer->name . '.');
        }

        $game = $this->gameRepository->getGameWhereUserIsSecondPlayer($firstPlayer->id);
        
        if ($game) {
            throw new YouСannotAgreeTwoGamesAtOnceException
            ('You have already been offered to play. Accept the offer or refuse the offer. ' . 'Offer from '. $game->firstPlayer->name . '.');
        }

        $this->userService->putUserInStandbyMode($firstPlayer);
        $this->userService->putUserInStandbyMode($secondPlayer);

        $attributes = array('player_1' => $firstPlayer->id, 'player_2' => $secondPlayer->id);
        $game = $this->gameService->createGame($attributes);

        InviteToPlayEvent::dispatch( GameResource::make($game));

        return $game;
    }
}