<?php

namespace App\Repositories;

use App\Models\Game as Model;

/**
 * Repository for working with the game. Can output datasets. Cannot create/modify games.
 */
class GameRepository extends CoreRepository
{
    /**
     * Returns a string with the model class.
     */
    public function getModelClass(): string
    {
        return Model::class;
    }

    /**
    * Gets the game the user is in.
    */
    public function getGameWhereUserIsIn(int $id): ?Model
    {   
        $columns = ['id', 'player_1', 'player_2', 'status', 'winned_player', 'leaving_player'];
       
        $result = $this->startConditions()
                       ->where('player_1', $id)
                       ->orWhere('player_2', $id)
                       ->select($columns)
                       ->latest()
                       ->first();

        return $result;
    }
   
    /**
     * Gets the game where the user is the first player.
     */
    public function getGameWhereUserIsFirstPlayer(int $id): ?Model
    {   
        $columns = ['id', 'player_1', 'player_2', 'status', 'winned_player', 'leaving_player'];
       
        $result = $this->startConditions()
                       ->whereIn('status', [Model::WAITING_PLAYER, Model::IN_PROCESS])
                       ->where('player_1', $id)
                       ->select($columns)
                       ->first();

        return $result;
    }

    /**
     * Gets the game where the user is the second player.
     */
    public function getGameWhereUserIsSecondPlayer(int $id): ?Model
    {   
        $columns = ['id', 'player_1', 'player_2', 'status', 'winned_player', 'leaving_player'];
       
        $result = $this->startConditions()
                       ->whereIn('status', [Model::WAITING_PLAYER, Model::IN_PROCESS])
                       ->where('player_2', $id)
                       ->select($columns)
                       ->first();

        return $result;
    }
    
}