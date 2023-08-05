<?php

namespace App\Repositories;

use App\Models\User as Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repository for working with the user. Can output datasets. Cannot create/modify users.
 */
class UserRepository extends CoreRepository
{
    /**
     * Returns a string with the model class.
     */
    public function getModelClass(): string
    {
        return Model::class;
    }

    /**
     * Get a list of users who are online using pagination.
     */
    public function getEveryoneWhoOnlineWithPaginated(int $perPage = null): LengthAwarePaginator 
    {   
        $columns = ['id', 'name', 'online_status', 'game_status'];
       
        $result = $this->startConditions()
                       ->where('online_status', Model::ONLINE)
                       ->select($columns)
                       ->paginate($perPage);
       
        return $result;
    }

}