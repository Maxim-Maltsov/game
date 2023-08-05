<?php

namespace App\Services;

use App\Models\Round;
use App\Repositories\RoundRepository;

/**
 * Contains methods for updating round data and other business logic related to rounds.
 */
class RoundService 
{   
    /**
     * Finishes the round if one of the players leaves the game early.
     */
    public function finishRoundEarly(Round $activeRound, array $players): void
    {
        $activeRound->status = Round::FINISHED;
        $activeRound->winned_player = $players['winned_player']; 
        $activeRound->save();
    }

}