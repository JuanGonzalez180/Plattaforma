<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function getCategoryList()
    {
        $parents = Category::whereNull('parent_id')
            ->where('status', Category::CATEGORY_PUBLISH)
            ->orderBy('name','ASC')
            ->pluck('id');

        $parent_array = [];

        foreach($parents as $parent) {

            $childs = DB::select('call get_child_type_categoty("'.$parent.'")');

            if(count($childs) <= 1)
                continue;

            $parent_array[] = json_decode( json_encode($childs), true);
        }

        return $parent_array;

    }

    public function getProjectList()
    {
        $parents = TypeProject::whereNull('parent_id')
            ->where('status', TypeProject::TYPEPROJECT_PUBLISH)
            ->orderBy('name','ASC')
            ->pluck('id');

        $parent_array = [];

        foreach($parents as $parent) {

            $childs = DB::select('call get_child_type_project("'.$parent.'")');

            if(count($childs) <= 1)
                continue;
            $parent_array[] = json_decode( json_encode($childs), true);
        }

        return $parent_array;

    }

    public function getTypeCompany()
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $types_entities = Company::select('types_entities.*')->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->distinct('types_entities.id')
            ->orderBy('name','ASC')
            ->get();

        $array = [];

        foreach($types_entities as $type_entity) {
            $array[] = array(
                "name"      => $type_entity->name,
                "slug"      => $type_entity->slug,
                "status"    => $type_entity->status,
                "entities"  => DB::select('call get_child_type_entity("'.$type_entity->id.'")')
            );
        }

        return $array;

    }

    public function index()
    {
        $result = array(
            "project_list"    => $this->getProjectList(),
            "category_list"   => $this->getCategoryList(),
            "company_list"    => $this->getTypeCompany()
        );

        return $result;
    }
}
