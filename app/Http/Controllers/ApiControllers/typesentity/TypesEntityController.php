<?php

namespace App\Http\Controllers\ApiControllers\typesentity;

use App\Models\TypesEntity;
use App\Http\Controllers\ApiControllers\ApiController;

class TypesEntityController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $typesEntity = TypesEntity::all();
        return $this->showAll($typesEntity);
    }
}
