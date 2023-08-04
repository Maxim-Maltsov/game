<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Repository for working with the entity. Can output datasets. Cannot create/modify entities.
 * 
 * @package App\Repositories
 */
abstract class CoreRepository
{
   
    protected Model $model;
    
    /**
     * CoreRepository constructor.
     */
    public function __construct() 
    {
        $this->model = app($this->getModelClass());
    }

    /**
     * @return mixed
     */
    abstract protected function getModelClass();
    
    /**
     * @return Model|\Illuminate\Foundation\Application|mixed
     */
    protected function startConditions()
    {
        return clone $this->model;
    }
    
}
