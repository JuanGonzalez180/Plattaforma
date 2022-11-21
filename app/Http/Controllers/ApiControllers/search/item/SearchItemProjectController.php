<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use App\Models\Tags;
use App\Models\Company;
use App\Models\Projects;
use App\Models\TypesEntity;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;
class SearchItemProjectController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        $projects = $this->getProjectEnabled();

        $search    = !isset($request->search) ? null : $request->search;
        $status    = ($request->status == 'all') ? null : $request->status;

        if (!is_null($status)) {
            $projects = $this->getProjectsStatus($projects, $status);
        }

        if (!is_null($search)) {
            $projects = $this->getProjectsSearchNameItem($projects, $search);
        }

        $projects = Projects::whereIn('id', $projects)
            ->orderBy('name', 'asc')
            ->get();

        
        return $this->showAllTransformer($projects);
    }

    public function getProjectsSearchNameItem($projects, $search)
    {
        $projectName                = $this->getProjectsName($projects, $search);
        $projectDescription         = $this->getProjectsDescription($projects, $search);
        $projectCompaniesTags       = $this->getProjectsCompanyTags($projects, $search);
        $projectCompaniesName       = $this->getProjectsCompanyName($projects, $search);

        $projects = array_unique(Arr::collapse([
            $projectName,
            $projectDescription,
            $projectCompaniesTags,
            $projectCompaniesName
        ]));

        return $projects;
    }

    public function getProjectsName($projects, $name)
    {
        return Projects::whereIn('id', $projects)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('id');
    }

    public function getProjectsDescription($projects, $name)
    {
        return Projects::whereIn('id', $projects)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('id');
    }

    public function getProjectsCompanyTags($projects, $name)
    {
        return Tags::where('tags.tagsable_type', Company::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->join('companies', 'companies.id', '=', 'tags.tagsable_id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('projects', 'projects.company_id', '=', 'companies.id')
            ->whereIn('projects.id', $projects)
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('projects.id');
    }

    public function getProjectsCompanyName($projects, $name)
    {
        return Projects::whereIn('projects.id', $projects)
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->pluck('projects.id');
    }

    public function getProjectEnabled()
    {
        $user = $this->validateUser();

        $type = ($user->userType() == 'demanda') ? 'oferta' : 'demanda';

        return Projects::where('projects.visible', Projects::PROJECTS_VISIBLE)
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.slug', $type)
            ->pluck('projects.id');
    }

    public function getProjectsStatus($projects, $status)
    {
        if ($status == Projects::TECHNICAL_SPECIFICATIONS) {
            $status = Projects::TECHNICAL_SPECIFICATIONS;
        } else if ($status == Projects::IN_CONSTRUCTION) {
            $status = Projects::IN_CONSTRUCTION;
        } else if ($status == Projects::POST_CONSTRUCTION_AND_MAINTENANCE) {
            $status = Projects::POST_CONSTRUCTION_AND_MAINTENANCE;
        }

        return Projects::whereIn('id', $projects)
            ->where('projects.status', '=', $status)
            ->pluck('id');
    }
}
