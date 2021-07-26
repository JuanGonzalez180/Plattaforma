<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Projects;
use App\Models\TypeProject;
use App\Models\TypesEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchLikeItemController extends ApiController
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
        $user           = $this->validateUser();
        $type_user      = $user->userType();

        $search_item    = $request->search_item;
        $type_consult   = $request->type_consult;

        $result = "";
        if($type_user == 'oferta')
        {
            if(isset($type_consult))
            {
                if($type_consult == 'companies')
                {
                    //Busqueda por las compañias
                    $result = $this->getCompanies($search_item);
                }
                else if($type_consult == 'projets')
                {
                    //Busca por los proyectos
                    $result = $this->getProjects($search_item);
                }
                else if($type_consult == 'tenders')
                {
                    var_dump('tenders');
                }
            }
            else if(!isset($type_consult))
            {

            }
        }
        else
        {

        };

        return $result;
    }

    public function getCompanies($like)
    {
        $user      = $this->validateUser();

        $type_slug = ($user->userType() == 'demanda')? 'oferta' : 'demanda';
        
        $companies = Company::select('companies.*')
            ->where('companies.status',Company::COMPANY_APPROVED)
            ->where(strtolower('companies.name'),'LIKE','%'.strtolower($like).'%')
            ->join('types_entities', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types_entities.type_id', '=', 'types.id')
            ->where('types.slug', $type_slug)
            ->distinct('types_entities.id')
            ->orderBy('companies.name','ASC')
            ->get();

        return $this->showAllPaginate($companies);
    }

    public function getProjects($like)
    {
        //busca por el nombre del proyecto
        $projetName         = $this->getNameProjects($like);
        //busca proyectos relacionados con el nombre del tipo de proyecto
        $projetTypeProject  = $this->getTypeProjects($like);

        //hace un merge de $projetName y $projetTypeProject, quita los id repetidos, dejando uno de cada uno
        $projects_ids       = array_unique(array_merge(json_decode($projetName), json_decode($projetTypeProject)));

        $projects           = Projects::whereIn('id', $projects_ids)->get();

        return $this->showAllPaginate($projects);
    }

    //busca los proyectos por medio delnombre del proyecto
    public function getNameProjects($like)
    {
        $projets = Projects::select('id')
            ->where(strtolower('name'),'LIKE','%'.strtolower($like).'%')
            ->where('visible', Projects::PROJECTS_VISIBLE)
            ->pluck('companies.id');

        return $projets;
    }

    //busca los proyectos por medio del tipo de proyecto
    public function getTypeProjects($like)
    {
        $projects = TypeProject::select('projects.id')
            ->where(strtolower('type_projects.name'),'LIKE','%'.strtolower($like).'%')
            ->where('type_projects.status', TypeProject::TYPEPROJECT_PUBLISH)
            ->join('projects_type_project','projects_type_project.type_project_id','=','type_projects.id')
            ->join('projects','projects.id','=','projects_type_project.projects_id')
            ->where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->distinct('projects_type_project.projects_id')
            ->pluck('projects.id');

        return $projects;
    }

    // public function getChildProjects($project_id)
    // {
    //     $childs = DB::select('call get_child_type_project("'.$id.'")');

    //     foreach ($childs as $key => $child)
    //     {
    //         if($id > $child->id)
    //             unset($childs[$key]);
    //     };

    //     return json_decode( json_encode($childs), true);
    // }

    public function getTenders($like)
    {

    }



   
}
