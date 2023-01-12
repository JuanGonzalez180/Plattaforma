<?php

namespace App\Http\Controllers\WebControllers\typesentity;

use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TypesEntityApiControllers extends Controller
{
    public function __invoke()
    {
        // Tipos de entidad tipo demanda.
        $typesEntityProject = $this->getTypeEntity('Demanda','asc');
        // Tipos de entidad tipo producto.
        $typesEntityProduct = $this->getTypeEntity('Oferta','desc');

        $typesEntity  = $typesEntityProject->merge($typesEntityProduct);

        return response()->json($typesEntity, 200);
    }

    public function getTypeEntity($type, $order)
    {
        $typesEntity = TypesEntity::select('types_entities.id','types_entities.name')->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
        ->join('types','types.id','=','types_entities.type_id')
        ->where('types.name',$type)
        ->orderBy('types_entities.name',$order)
        ->get();

        return $typesEntity;
    }
}
