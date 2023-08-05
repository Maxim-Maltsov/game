<?php

namespace App\Actions;

use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Repositories\RoundRepository;
use App\Services\GameService;
use App\Services\RefereeService;
use App\Services\RoundService;
use App\Services\UserService;

/**
 * The player leaves the game before it ends.
 */
class LeaveGameAction 
{   
    /**
     * LeaveGameAction constructor.
     */
    public function __construct( private UserService $userService, 
                                 private RefereeService $referee, 
                                 private RoundRepository $roundRepository,
                                 private RoundService $roundService,
                                 private GameService $gameService ){}
    
    /**
     * Triggers the action that allows you to leave the game before it ends.
     */
    public function handle(Game $game)
    {   
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
       
        $this->userService->makeUserFree($firstPlayer);
        $this->userService->makeUserFree($secondPlayer);

        $activeRound = $this->roundRepository->getActiveRound($game->id);
        $players = $this->referee->defineWinnerAndLoser($game);
        
        $this->roundService->finishRoundEarly($activeRound, $players);
        $this->gameService->finishGameEarly($game, $players);

        FirstPlayerLeavedGameEvent::dispatch(GameResource::make($game));
        SecondPlayerLeavedGameEvent::dispatch(GameResource::make($game));
    }
}