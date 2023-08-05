<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;

/**
 * Defines the winner of the game, the winner of the round. Monitors compliance with all the rules of the game.
 */
class RefereeService 
{   
    /**
     *  Defines the winner and the loser in case of early exit from the game. Returns an array with the IDs of the winner player and the player who leaves the game.
     */
    public function defineWinnerAndLoser(Game $game): array
    {  
        $players = [];

        $leavingPlayer = Auth::id();
        $winnedPlayer = ($leavingPlayer == $game->player_1)? $game->player_2 : $game->player_1;

        $players['winned_player'] = $winnedPlayer;
        $players['leaving_player'] = $leavingPlayer;

        return $players;
    }
}