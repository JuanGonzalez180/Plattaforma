<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersFilter;

use JWTAuth;
use App\Models\Tenders;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use App\Http\Controllers\ApiControllers\ApiController;

class tendersFilterController extends ApiController
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
        $filter = null;

        $filter['status']       = $this->tenderStatus();
        $filter['projects']     = $this->getProjects();

        return $filter;
    }

    public function tenderStatus()
    {
        $status[] = [
            "id" => TendersVersions::LICITACION_PUBLISH,
            "name" => 'Publicada',
        ];
        $status[] = [
            "id" => TendersVersions::LICITACION_CLOSED,
            "name" => 'En evaluación',
        ];
        $status[] = [
            "id" => TendersVersions::LICITACION_FINISHED,
            "name" => 'Adjudicada',
        ];
        $status[] = [
            "id" => TendersVersions::LICITACION_DISABLED,
            "name" => 'Suspendida',
        ];
        $status[] = [
            "id" => TendersVersions::LICITACION_DESERTED,
            "name" => 'Desierta',
        ];


        return $status;
    }

    public function getProjects()
    {
        $tendersCompany = $this->getTendersCompany();
        
        $projectsId = Tenders::select('project_id')
            ->whereIn('id', $tendersCompany)
            ->pluck('project_id');

        return Projects::select('id','name')
            ->whereIn('id',$projectsId)
            ->orderBy('name','asc')
            ->get();
    }

    public function getTendersCompany()
    {
        $user       = $this->validateUser();
        $company_id = $user->companyId();

        $tenders_company = TendersCompanies::where('company_id', $company_id);

        if(!$user->getAdminUser())
        {
            $tenders_company = $tenders_company->where('user_company_id', '=', $user->id);
        }
  
        $tenders_company = $tenders_company
            ->pluck('tender_id');

        return $tenders_company;
    }
}
