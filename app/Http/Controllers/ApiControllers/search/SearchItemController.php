<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Models\CategoryTenders;
use App\Models\TendersVersions;
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

    public function index(Request $request)
    {
        $user       = $this->validateUser();
        $type_user  = $user->userType();

        
        if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id)){

            var_dump('solo comunity_id');

            $result['company_list']             = $this->getTypeCompany($request->comunity_id);

        }else if((isset($request->type_project) || isset($request->comunity_id)) && !isset($request->category_id)){

            var_dump('tipo de proyecto y/o comunity_id');

            if($request->comunity_id)
                $result['company_list']         = $this->getTypeCompany($request->comunity_id);

            if(($request->type_project) && ($request->type_consult == 'projects'))
                $result['project_list']         = $this->getProjectIdList($request->type_project);

            if(($request->type_project) && ($request->type_consult == 'tenders'))
                $result['project_tender_list']  = $this->getTenderProjectIdList($request->type_project);  

        }else if((isset($request->category_id) || isset($request->comunity_id)) && !isset($request->type_project) ){

            var_dump('category_id y/o comunity_id');
            
            if($request->comunity_id)
                $result['company_list']         = $this->getTypeCompany($request->comunity_id);

            if(($request->category_id) && ($type_user == 'demanda'))
                $result['category_list']        = $this->getCategoryIdList($request->category_id);

            if(($request->category_id) && ($type_user == 'oferta'))
                $result['category_tender_list'] = $this->getTenderCategoryIdList($request->category_id);
        }


        return $result;
    }

    public function getCategoryIdList($id)
    {
        $childs = DB::select('call get_child_type_categoty("'.$id.'")');
        return json_decode( json_encode($childs), true);
    }

    public function getProjectIdList($id)
    {
        $childs = DB::select('call get_child_type_project("'.$id.'")');

        return json_decode( json_encode($childs), true);
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

    public function getTenderCategoryIdList($id) {

        $tenders = CategoryTenders::select('tenders.*')
            ->where('category_tenders.category_id', $id)
            ->join('tenders','category_tenders.tenders_id','=','tenders.id')
            ->pluck('tenders.id');

        $tenders = Tenders::whereIn('id',$tenders)->get();

        foreach($tenders as $key =>$tender){
            if($tender->tendersVersionLast()->status != TendersVersions::LICITACION_PUBLISH)
                unset($tenders[$key]);
        };

        return $this->showAllPaginate($tenders);
    }

    public function getTenderProjectIdList($id) {

        $tenders = Tenders::where('project_id',$id)->get();

        foreach($tenders as $key =>$tender){
            if($tender->tendersVersionLast()->status != TendersVersions::LICITACION_PUBLISH)
                unset($tenders[$key]);
        };
        
        return $this->showAllPaginate($tenders);
    }

    

    public function getTypeCompany($id)
    {
        $user = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $types_entities = Company::select('types_entities.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.id', $id)
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

    
}
