<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Projects;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemTenderController extends ApiController
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

        // * Estados de la licitación.
        $tenderStatus = [
            TendersVersions::LICITACION_PUBLISH,    // ->Publicada
            TendersVersions::LICITACION_CLOSED,     // ->En Evaluación
            TendersVersions::LICITACION_FINISHED,   // ->Adjudicada
            TendersVersions::LICITACION_DECLINED,   // ->Declinada
            TendersVersions::LICITACION_DISABLED,   // ->Suspendida
            TendersVersions::LICITACION_DESERTED    // ->Desierta
        ];

        // * Criterios de busqueda.

        // ->Palabra clave de busqueda de la licitación.
        $search         = !isset($request->search) ? null : $request->search;
        // ->Estado de la licitación.
        $status         = ($request->status == 'all') ? null : $request->status;
        // ->Id del proyecto de la licitación.
        $project        = ($request->project == 'all') ? null : $request->project;
        // ->Rango de fecha del dia de cierre de la licitación.
        $date           = $this->getAssignDate($request->date_start, $request->date_end);

        // * Proyectos Activos
        $projectActive  = $this->getActiveProjects($project);

        if(!is_null($status))
        {
            $tender =  $this->getFullTender($status, $date, $search, $projectActive);
        }
        else
        {
            $tender = null;
            foreach ($tenderStatus as  $key => $status)
            {
                $tenderold  =  $this->getFullTender($status, $date, $search, $projectActive);
                $tender     = ($key>0)? $tender->merge($tenderold) : $tenderold;
            }
        }

        return $this->showAllPaginate($tender);
    }

    public function getFullTender($status, $date, $search, $projectActive)
    {
        // ->Ultimas versiones de cada licitación(tender_versions) segun el estado.
        $tenderVersionLast  = $this->getTenderLastStatusVersion($status);
        // ->Filtra por rango de fecha.
        $tenderStatus      = $this->getTenderVersionStatus($status, $tenderVersionLast, $date);
        // ->Filtra por criterios de busqueda.
        $tenderStatus      = $this->getTendersSearchNameItem($tenderStatus, $search, $status);

        return Tenders::whereIn('id',$tenderStatus)
            ->whereIn('project_id',$projectActive)
            ->where('type', Tenders::TYPE_PUBLIC)
            ->orderBy('created_at','desc')
            ->get();
    }


    public function getTenderVersionStatus($status, $tenderVersionLast, $date)
    {
        // ->Fecha de inicio
        $date_start = null;
        // ->Fecha final
        $date_end   = null;

        if(!is_null($date))
        {
            $date_start = (!is_null($date['date_start'])) ? $date['date_start'] : null;
            $date_end   = (!is_null($date['date_end'])) ? $date['date_end'] : null;
        }

        $tender = TendersVersions::select('tenders_id');

        if(!is_null($status))
            $tender = $tender->where('status', $status);

        $tender = $tender->whereIn('id', $tenderVersionLast);

        if (!is_null($date_start) && is_null($date_end)) {
            $tender = $tender->whereDate('tenders_versions.date', '>=', $date_start);
        } else if (!is_null($date_start) && !is_null($date_end)) {
            $tender = $tender->whereBetween('tenders_versions.date', [$date_start, $date_end]);
        } else if (is_null($date_start) && !is_null($date_end)) {
            $tender = $tender->whereDate('tenders_versions.date', '<=', $date_end);
        }

        return $tender->pluck('tenders_id');
    }

    public function getTenderLastVersion()
    {
        $tenders = DB::table('tenders_versions as a')
            ->select(
                DB::raw('max(a.created_at), a.tenders_id'),
                DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
                where `c`.`status` NOT IN ('" . TendersVersions::LICITACION_CREATED . ".".TendersVersions::LICITACION_PUBLISH."')  
                and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
            )
            ->whereNotIn('a.status', [TendersVersions::LICITACION_CREATED, TendersVersions::LICITACION_PUBLISH])
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` IN ('" . TendersVersions::LICITACION_CREATED . ".".TendersVersions::LICITACION_PUBLISH."')  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('version_id');

        return $tenders;
        // $tenders = DB::table('tenders_versions as a')
        //     ->select(
        //         DB::raw('max(a.created_at), a.tenders_id'),
        //         DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
        //         where `c`.`status` != '" . TendersVersions::LICITACION_CREATED . "'  
        //         and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
        //     )
        //     ->where('a.status', '<>', TendersVersions::LICITACION_CREATED)
        //     ->where((function ($query) {
        //         $query->select(
        //             DB::raw("COUNT(*) from `tenders_versions` as `b` 
        //             where `b`.`status` = '" . TendersVersions::LICITACION_CREATED . "'  
        //             and `b`.`tenders_id` = a.tenders_id")
        //         );
        //     }), '=', 0)
        //     ->groupBy('a.tenders_id')
        //     ->pluck('version_id');

        // return $tenders;
    }

    public function getTenderLastStatusVersion($tenderStatus)
    {
        $tenders = DB::table('tenders_versions as a')
            ->select(
                DB::raw('max(a.created_at) as date, a.tenders_id'),
                DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
                where `c`.`status` = '" . $tenderStatus . "'  
                and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
            )
            ->where('a.status', $tenderStatus)
            ->where((function ($query) use ($tenderStatus) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` <> '" . $tenderStatus . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->orderBy('a.updated_at','desc')
            ->pluck('version_id');
        // $tenders = DB::table('tenders_versions as a')
        //     ->select(
        //         DB::raw('max(a.created_at), a.tenders_id'),
        //         DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
        //         where `c`.`status` != '" . TendersVersions::LICITACION_CREATED . "'  
        //         and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
        //     )
        //     ->where('a.status', '<>', TendersVersions::LICITACION_CREATED)
        //     ->where((function ($query) {
        //         $query->select(
        //             DB::raw("COUNT(*) from `tenders_versions` as `b` 
        //             where `b`.`status` = '" . TendersVersions::LICITACION_CREATED . "'  
        //             and `b`.`tenders_id` = a.tenders_id")
        //         );
        //     }), '=', 0)
        //     ->groupBy('a.tenders_id')
        //     ->pluck('version_id');

        return $tenders;
    }

    public function getAssignDate($date_start, $date_end)
    {
        $date = null;
        if (isset($date_start) || isset($date_end)) {
            $date['date_start'] = !isset($date_start) ? null : $date_start;
            $date['date_end']   = !isset($date_end) ? null : $date_end;
        };

        return $date;
    }

    // Trae los IDS de los proyectos activos.
    public function getActiveProjects($project_id)
    {
        $dateNow = Carbon::now()->format('Y-m-d');
        
        $projects = (!is_null($project_id)) 
            ?  Projects::where('id', $project_id)->where('date_start','<=',$dateNow)->where('date_end','>=',$dateNow) 
            : Projects::where('date_start','<=',$dateNow)->where('date_end','>=',$dateNow);

        return $projects->distinct('projects.id')
            ->pluck('projects.id');
    }

    public function getTendersSearchNameItem($tenders, $search, $status)
    {
        //nombre de licitacion
        $tendersName                = $this->getTendersName($tenders, $search);
        //descripcion de la licitacion
        $tendersDescription         = $this->getTendersDescription($tenders, $search);
        //nombre la compañia de la licitacion
        $tendersCompanyName         = $this->getTenderCompanyName($tenders, $search);
        //nombre de proyecto de la licitacion
        $tendersProjectName         = $this->getTenderProjectName($tenders, $search);
        //tags de la licitacion
        $tenderVersionTags          = $this->getTenderVersionTags($tenders, $search, $status);

        $tenders = array_unique(Arr::collapse([
            $tendersName,
            $tendersDescription,
            $tendersCompanyName,
            $tendersProjectName,
            $tenderVersionTags
        ]));

        return $tenders;
    }

    // Busca por el nombre de la licitación
    public function getTendersName($tenders, $name)
    {
        return Tenders::whereIn('id', $tenders)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    // Busca por la descripción de la licitación
    public function getTendersDescription($tenders, $name)
    {
        return Tenders::whereIn('id', $tenders)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    // Buscar por el nombre de la compañia de la licitación
    public function getTenderCompanyName($tenders, $name)
    {
        return Tenders::whereIn('tenders.id', $tenders)
            ->join('companies', 'companies.id', '=', 'tenders.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            // ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->pluck('tenders.id');
    }

    // Busca por el nombre del proyecto de la licitación
    public function getTenderProjectName($tenders, $name)
    {
        return Tenders::whereIn('tenders.id', $tenders)
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->where(strtolower('projects.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('tenders.id');
    }

    // Busca por el tag de la licitación
    public function getTenderVersionTags($tenders, $name, $status)
    {
        $tenderVersion = $this->getTenderLastStatusVersion($status);

        return Tenders::whereIn('tenders.id', $tenders)
            ->join('tenders_versions', 'tenders_versions.tenders_id', '=', 'tenders.id')
            ->whereIn('tenders_versions.id', $tenderVersion)
            ->join('tags', 'tags.tagsable_id', '=', 'tenders_versions.id')
            ->where('tags.tagsable_type', '=', 'App\Models\TendersVersions')
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('tenders.id');
    }
    
}
