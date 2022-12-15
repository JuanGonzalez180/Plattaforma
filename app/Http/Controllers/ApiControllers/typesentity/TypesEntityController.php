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

        // Tipos de entidad tipo demanda.
        $typesEntityProject = $this->getTypeEntity('Demanda');
        // Tipos de entidad tipo producto.
        $typesEntityProduct = $this->getTypeEntity('Oferta');

        $typesEntity  = $typesEntityProject->merge($typesEntityProduct);

        return $this->showAll($typesEntity);
    }

    public function getTypeEntity($type)
    {
        $typesEntity = TypesEntity::select('types_entities.*')->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
        ->join('types','types.id','=','types_entities.type_id')
        ->where('types.name',$type)
        ->orderBy('types_entities.name','asc')
        ->get();

        return $typesEntity;
    }
}
