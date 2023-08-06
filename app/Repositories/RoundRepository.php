<?php

namespace App\Repositories;

use App\Models\Round as Model;


/**
 * Repository for working with the round. Can output datasets. Cannot create/modify rounds.
 */
class RoundRepository extends CoreRepository
{
    /**
     * Returns a string with the model class.
     */
    public function getModelClass(): string
    {
        return Model::class;
    }

    /**
     * Get an active round of the game.
     */
    public function getActiveRound(int $gameId): ?Model
    {   
        $columns = ['id', 'game_id', 'number', 'status', 'winned_player', 'draw'];
       
        $result = $this->startConditions()
                       ->where('game_id', $gameId)
                       ->where('status', Model::NO_FINISHED)
                       ->select($columns)
                       ->first();
       
        return $result;
    }

}