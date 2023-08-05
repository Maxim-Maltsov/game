<?php

namespace App\Services;

use App\Models\Game;
use Carbon\Carbon;

/**
 * Contains methods for updating game data and other business logic related to games.
 */
class GameService
{   
    /**
     * Finishes the game if one of the players leaves the game early.
     */
    public function finishGameEarly(Game $game, array $players): void
    {
        $game->status = Game::FINISHED;
        $game->end = Carbon::now();
        $game->winned_player = $players['winned_player'];
        $game->leaving_player = $players['leaving_player'];
        $game->need_start_new_round = Game::NO;
        $game->save();
    }

}