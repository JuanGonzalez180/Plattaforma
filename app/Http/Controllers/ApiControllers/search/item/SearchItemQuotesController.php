<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Quotes;
use App\Models\Projects;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemQuotesController extends ApiController
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
        // Palabra clave de busqueda de la cotización.
        $search     = !isset($request->search) ? null : $request->search;
        // Estado de la cotización.
        $status     = ($request->status == 'all') ? null : $request->status;
        // Id del proyecto de la cotización.
        $project    = ($request->project == 'all') ? null : $request->project;
        // Rango de fecha del dia de cierre de la cotización.
        $date       = $this->getAssignDate($request->date_start, $request->date_end);

        // Proyectos Activos
        $projectActive          = $this->getActiveProjects($project);

        // Ultimas versiones de cada cotización.
        $quotesVersionLast      = $this->getQuotesPublishVersion();

        $quoteVersionStatus    = $this->getQuoteVersionStatus($status, $quotesVersionLast, $date);

        $quote = $this->getQuote($quoteVersionStatus, $projectActive, $search);

        return $this->showAllPaginate($quote);
    }

    public function getQuote($quoteVersionStatus, $projectActive, $search)
    {
        $quote = $this->getQuoteSearchNameItem($quoteVersionStatus, $search);

        return Quotes::whereIn('id',$quote)
            ->whereIn('project_id',$projectActive)
            ->where('type', Quotes::TYPE_PUBLIC)
            ->get();

    }

    public function getQuoteSearchNameItem($quotes, $search)
    {
        //nombre de COTIZACIÓN
        $quotesName                = $this->getQuotesName($quotes, $search);
        //descripcion de la COTIZACIÓN
        $quotesDescription         = $this->getQuotesDescription($quotes, $search);
        //nombre la compañia de la COTIZACIÓN
        $quotesCompanyName         = $this->getQuoteCompanyName($quotes, $search);
        //nombre de proyecto de la COTIZACIÓN
        $quotesProjectName         = $this->getQuotesProjectName($quotes, $search);
        //tags de la COTIZACIÓN
        $quotesVersionTags          = $this->getQuoteVersionTag($quotes, $search);

        $quotes = array_unique(Arr::collapse([
            $quotesName,
            $quotesDescription,
            $quotesCompanyName,
            $quotesProjectName,
            $quotesVersionTags
        ]));

        return $quotes;
    }

    // Busca por el nombre de la cotización
    public function getQuotesName($quotes, $name)
    {
        return Quotes::whereIn('id', $quotes)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    // Busca por la descripción de la cotización
    public function getQuotesDescription($quotes, $name)
    {
        return Quotes::whereIn('id', $quotes)
            ->where(strtolower('description'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    // Buscar por el nombre de la compañia de la cotización
    public function getQuoteCompanyName($quotes, $name)
    {
        return Quotes::whereIn('quotes.id', $quotes)
            ->join('companies', 'companies.id', '=', 'quotes.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('quotes.id');
    }

    // Busca por el nombre del proyecto de la cotización
    public function getQuotesProjectName($quotes, $name)
    {
        return Quotes::whereIn('quotes.id', $quotes)
            ->join('projects', 'projects.id', '=', 'quotes.project_id')
            ->where(strtolower('projects.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('projects.visible', '=', Projects::PROJECTS_VISIBLE)
            ->pluck('quotes.id');
    }

    // Busca por el tag de la cotización
    public function getQuoteVersionTag($quotes, $name)
    {
        return Quotes::whereIn('quotes.id', $quotes)
            ->join('quotes_versions', 'quotes_versions.quotes_id', '=', 'quotes.id')
            ->whereIn('quotes_versions.id', $this->getQuotesPublishVersion())
            ->join('tags', 'tags.tagsable_id', '=', 'quotes_versions.id')
            ->where('tags.tagsable_type', '=', 'App\Models\QuotesVersions')
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('quotes.id');
    }

    public function getQuoteVersionStatus($status, $quotesVersionLast, $date)
    {
        // Fecha de inicio
        $date_start = null;
        // Fecha final
        $date_end   = null;

        if(!is_null($date))
        {
            $date_start = (!is_null($date['date_start'])) ? $date['date_start'] : null;
            $date_end   = (!is_null($date['date_end'])) ? $date['date_end'] : null;
        }

        $quote = QuotesVersions::select('quotes_id');

        if(!is_null($status))
            $quote = $quote->where('status', $status);

        $quote = $quote->whereIn('id', $quotesVersionLast);

        if (!is_null($date_start) && is_null($date_end)) {
            $quote = $quote->whereDate('quotes_versions.date', '>=', $date_start);
        } else if (!is_null($date_start) && !is_null($date_end)) {
            $quote = $quote->whereBetween('quotes_versions.date', [$date_start, $date_end]);
        } else if (is_null($date_start) && !is_null($date_end)) {
            $quote = $quote->whereDate('quotes_versions.date', '<=', $date_end);
        }

        return $quote->pluck('quotes_id');
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

    public function getAssignDate($date_start, $date_end)
    {
        $date = null;
        if (isset($date_start) || isset($date_end)) {
            $date['date_start'] = !isset($date_start) ? null : $date_start;
            $date['date_end']   = !isset($date_end) ? null : $date_end;
        };

        return $date;
    }

    public function getQuotesPublishVersion()
    {
        $quotesVersion = DB::table('quotes_versions as a')
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
            ->pluck('version_id');

        return $quotesVersion;
    }
}
