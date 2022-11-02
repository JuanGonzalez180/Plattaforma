<?php

namespace App\Http\Controllers\ApiControllers\search\item\filters;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\Tenders;
use App\Models\Projects;
use App\Models\Quotes;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\QuotesVersions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class ItemFilterController extends ApiController
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

        switch ($request->item)
        {
            case 'company':
                $filter['type_entity'] = $this->companyTypeEntity();
                break;
            case 'product':
                $filter['type_entity'] = $this->companyTypeEntity();
                break;
            case 'catalog':
                $filter['type_entity'] = $this->companyTypeEntity();
                break;
            case 'publication':
                $filter['type_entity'] = $this->companyTypeEntity();
                break;
            case 'project':
                $filter['status'] = $this->projectStatus();
                break;
            case 'tender':
                $filter['status']   = $this->tenderStatus();
                $filter['projects'] = $this->projectTender();
                break;
            case 'quote':
                $filter['status'] = $this->quoteStatus();
                $filter['projects'] = $this->projectQuote();
                break;
        }

        return $filter;
    }

    public function quoteStatus()
    {
        $status[] = [
            "id" => QuotesVersions::QUOTATION_PUBLISH,
            "name" => QuotesVersions::QUOTATION_PUBLISH,
        ];
        $status[] = [
            "id" => QuotesVersions::QUOTATION_CLOSED,
            "name" => QuotesVersions::QUOTATION_CLOSED,
        ];
        $status[] = [
            "id" => QuotesVersions::QUOTATION_FINISHED,
            "name" => QuotesVersions::QUOTATION_FINISHED,
        ];
        
        return $status;
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

    public function projectStatus()
    {
        $status[] = [
            "id" => Projects::TECHNICAL_SPECIFICATIONS,
            "name"  => 'Especificaciones Técnicas',
        ];

        $status[] = [
            "id" => Projects::IN_CONSTRUCTION,
            "name"  => 'En construcción ',
        ];

        $status[] = [
            "id" => Projects::POST_CONSTRUCTION_AND_MAINTENANCE,
            "name"  => 'Post-construcción y mantenimiento',
        ];

        return $status;
    }

    // Lista de proyectos activos. donde por lo menos existe uno o mas licitaciones.
    public function projectTender()
    {
        $projectsId = array_intersect(
            json_decode($this->getTenderProject()),//Lista de proyectos donde por lo menos existe uno o mas licitaciones.
            json_decode($this->activeProjects())//Lista de proyecto en curso.
        );

        return Projects::select('id','name')
            ->whereIn('id', $projectsId)
            ->orderBy('name','asc')
            ->get();
    }

    public function projectQuote()
    {
        $projectsId = array_intersect(
            json_decode($this->getQuoteProject()),//Lista de proyectos donde por lo menos existe uno o mas cotizaciones.
            json_decode($this->activeProjects())//Lista de proyecto en curso.
        );

        return Projects::select('id','name')
            ->whereIn('id', $projectsId)
            ->orderBy('name','asc')
            ->get();
    }

    // ID de proyectos activos.
    public function activeProjects()
    {
        $dateNow = Carbon::now()->format('Y-m-d');

        return Projects::where('date_start','<=',$dateNow)
        ->where('date_end','>=',$dateNow)
        ->pluck('id');
    }

    // Retorna el ID de los proyectos de cada licitación.
    public function getTenderProject()
    {
        return Tenders::select('project_id')
            ->whereIn('id', $this->getTenderLastVersion())
            ->distinct('project_id')
            ->pluck('project_id');
    }

    // Trae el ID de las licitaciones en todos sus estados.
    public function getTenderLastVersion()
    {
        $tenders = DB::table('tenders_versions as a')
            ->select(
                DB::raw('max(a.created_at), a.tenders_id'),
                DB::raw("(SELECT `c`.id from `tenders_versions` as `c` 
                where `c`.`status` != '" . TendersVersions::LICITACION_CREATED . "'  
                and `c`.`tenders_id` = a.tenders_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
            )
            ->where('a.status', '<>', TendersVersions::LICITACION_CREATED)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` = '" . TendersVersions::LICITACION_CREATED . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('tenders_id');

        return $tenders;
    }

    public function getQuoteProject()
    {
        return Quotes::select('project_id')
            ->whereIn('id', $this->getQuoteLastVersion())
            ->distinct('project_id')
            ->pluck('project_id');
    }

    public function getQuoteLastVersion()
    {
        $tenders = DB::table('quotes_versions as a')
            ->select(
                DB::raw('max(a.created_at), a.quotes_id'),
                DB::raw("(SELECT `c`.id from `quotes_versions` as `c` 
                where `c`.`status` != '" . QuotesVersions::QUOTATION_CREATED . "'  
                and `c`.`quotes_id` = a.quotes_id ORDER BY `c`.id DESC LIMIT 1) AS version_id")
            )
            ->where('a.status', '<>', QuotesVersions::QUOTATION_CREATED)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `quotes_versions` as `b` 
                    where `b`.`status` = '" . QuotesVersions::QUOTATION_CREATED . "'  
                    and `b`.`quotes_id` = a.quotes_id")
                );
            }), '=', 0)
            ->groupBy('a.quotes_id')
            ->pluck('quotes_id');

        return $tenders;
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
