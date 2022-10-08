<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Type;
use App\Models\Company;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemFilterController extends Controller
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function companyTypeEntity()
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();

        $type_company = $type_user ? 'demanda' : 'oferta';

        switch ($type_user) {
            case 'demanda':
                $type_company = 'oferta';
                break;
            case 'oferta':
                $type_company = 'demanda';
                break;
        }

        return $this->itemsTypeEntityCompany($type_company);
    }

    public function itemsTypeEntityCompany($type_company)
    {
        return Type::select('types_entities.id', 'types_entities.name')
            ->where('types.slug', $type_company)
            ->join('types_entities', 'types_entities.type_id', '=', 'types.id')
            ->where('types_entities.status', 'Publicado')
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('companies.status', 'Aprobado')
            ->distinct('types_entities.name')
            ->orderBy('types_entities.name', 'asc')
            ->get();
    }

    
}
