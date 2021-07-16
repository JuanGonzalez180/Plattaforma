<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\Tenders;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use App\Models\Products;
use App\Models\Company;
use App\Models\Projects;
use App\Models\TypeProject;
use Illuminate\Http\Request;


use App\Http\Controllers\ApiControllers\ApiController;

class SearchParameterController extends ApiController
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

        if( !isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result     = ($type_user == 'demanda') ? $this->getAllProducts() : $this->getAllTenders();
        }
        else if( isset($request->comunity_id) && !isset($request->type_project) && !isset($request->category_id))
        {
            $result     = $this->getTypeCompanyId($request->comunity_id);
        }
        else if( isset($request->comunity_id) && isset($request->type_project) && !isset($request->category_id))
        {
            $result     = (($type_user == 'oferta') && isset($request->comunity_id))
                ? $this->getProjects($request->type_project , $request->comunity_id)
                : $this->getProjects($request->type_project , null);

        }
        else if(!isset($request->comunity_id) && isset($request->type_project) && isset($request->category_id))
        {
            if(($type_user == 'oferta') && isset($request->comunity_id) )
            {
                // devolver las licitaciones que pertenecan a type_project, a la categoria de la licitacion y al tipo de entidad
                $result['tender_list'] = $this->getAllTenderList($request->type_project , null);
            }
            else
            {
                // devolver las licitaciones que pertenecan a type_project y a la categoria de la licitacion.
                $result['tender_list'] = $this->getAllTenderList($request->type_project , null);
            }
        }

        return $result;
    }

    public function getAllTenderList()
    {

    }

    public function getAllProducts()
    {
        $products = Products::where('status', Products::PRODUCT_PUBLISH)
                                        ->orderBy('id', 'desc')
                                        ->get();

        foreach( $products as $key => $product ){
            $product->user['url'] = $product->user->image ? url( 'storage/' . $product->user->image->url ) : null;
            $product->company;
            $product->company->image;
        }

        return $this->showAllPaginate($products);
    }

    public function getAllTenders()
    {
        $tenderLastVersionsPublish  = $this->getTendersLastVersionPublish();
        $tenders                    = Tenders::WhereIn('id', $tenderLastVersionsPublish)->get();

        return $this->showAllPaginate($tenders); 
    }

    public function getTendersLastVersionPublish()
    {
        $tenders = TendersVersions::select(DB::raw('max(created_at), tenders_id'))
            ->where('status',TendersVersions::LICITACION_PUBLISH)
            ->groupBy('tenders_id')
            ->pluck('tenders_id');

        return $tenders;
    }

    public function getTypeCompanyId($id)
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
                "id"        => $type_entity->id,
                "name"      => $type_entity->name,
                "slug"      => $type_entity->slug,
                "status"    => $type_entity->status,
                "entities"  => DB::select('call get_child_type_entity("'.$type_entity->id.'")')
            );
        }

        return $array;
    }

    public function getProjects($type_project_id, $comunity_id)
    {
        $type_project_ids = TypeProject::select('projects_type_project.projects_id')
            ->where('type_projects.id',$type_project_id)
            ->where('type_projects.status',TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects_type_project.projects_id');
        
        $projects = Projects::whereIn('id', $type_project_ids)
            ->where('visible', Projects::PROJECTS_VISIBLE);

        if(isset($comunity_id))
        {
            $companies = Company::select('companies.id')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->where('companies.type_entity_id', $comunity_id)
            ->distinct('companies.id')
            ->pluck('companies.id');

            $projects = $projects->whereIn('company_id', $companies);
        }

        $projects = $projects->get();

        return $this->showAllPaginate($projects); 
    }
}
