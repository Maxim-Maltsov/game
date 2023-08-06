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
}