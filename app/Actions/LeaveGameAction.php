<?php

namespace App\Actions;

use App\Events\FirstPlayerLeavedGameEvent;
use App\Events\SecondPlayerLeavedGameEvent;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Repositories\RoundRepository;
use App\Services\RefereeService;
use App\Services\RoundService;
use App\Services\UserService;
use Carbon\Carbon;

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
                                 private RoundService $roundService ){}
    
    /**
     * Triggers the action that allows you to leave the game before it ends.
     */
    public function handle(Game $game)
    {   
        $firstPlayer = $game->firstPlayer;
        $secondPlayer = $game->secondPlayer;
       
        $this->userService->makeUserFree($firstPlayer);
        $this->userService->makeUserFree($secondPlayer);

        $players = $this->referee->defineWinnerAndLoser($game);
        
        $activeRound = $this->roundRepository->getActiveRound($game->id);
        $this->roundService->finishRoundEarly($activeRound, $players);
   
        // Перенести логику обновления "статуса игры" и других её данных в класс "GameService".
        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->winned_player = $players['winned_player'];
        $game->leaving_player = $players['leaving_player'];
        $game->need_start_new_round = Game::NO;
        $game->save();

        FirstPlayerLeavedGameEvent::dispatch(GameResource::make($game));
        SecondPlayerLeavedGameEvent::dispatch(GameResource::make($game));
    }
}