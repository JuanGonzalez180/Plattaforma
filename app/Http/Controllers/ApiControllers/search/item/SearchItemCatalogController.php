<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use App\Models\Tags;
use App\Models\Company;
use App\Models\Catalogs;
use App\Models\TypesEntity;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemCatalogController extends ApiController
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
        $catalogs = $this->getCatalogEnabled();

        $search         = !isset($request->search) ? null : $request->search;

        $type_entity    = ($request->type_entity == 'all') ? null : $request->type_entity;

        if (!is_null($type_entity)) {
            $catalogs = $this->getCatalogTypeEntity($catalogs, $type_entity);
        }

        if (!is_null($search)) {
            $catalogs = $this->getCatalogsSearchNameItem($catalogs, $search);
        }

        $catalogs =  Catalogs::whereIn('id', $catalogs)
            ->orderBy('name', 'asc')
            ->get();

        return $this->showAllTransformer($catalogs);
    }

    public function getCatalogsSearchNameItem($catalogs, $search)
    {
        $catalogName            = $this->getCatalogName($catalogs, $search);
        $catalogCompanyName     = $this->getCatalogCompanyName($catalogs, $search);
        $catalogCompanyTag      = $this->getCatalogTag($catalogs, $search);

        $catalogs = array_unique(Arr::collapse([
            $catalogName,
            $catalogCompanyName,
            $catalogCompanyTag
        ]));

        return $catalogs;
    }

    public function getCatalogName($catalogs, $name)
    {
        return Catalogs::whereIn('catalogs.id', $catalogs)
            ->where(strtolower('catalogs.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('catalogs.id');
    }

    public function getCatalogCompanyName($catalogs, $name)
    {
        return Company::where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->whereIn('catalogs.id', $catalogs)
            ->pluck('catalogs.id');
    }

    public function getCatalogTag($catalogs, $search)
    {
        return Tags::where('tags.tagsable_type', Catalogs::class)
            ->where(strtolower('tags.name'), 'LIKE', '%' . strtolower($search) . '%')
            ->whereIn('tags.tagsable_id', $catalogs)
            ->join('catalogs', 'catalogs.id', '=', 'tags.tagsable_id')
            ->pluck('catalogs.id');
    }

    public function getCatalogTypeEntity($catalogs, $type_entity)
    {
        return TypesEntity::where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->where('types_entities.id', '=', $type_entity)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('companies.status', '=', Company::COMPANY_APPROVED)
            ->join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->whereIn('catalogs.id', $catalogs)
            ->pluck('catalogs.id');
    }

    public function getCatalogEnabled()
    {
        return Company::join('catalogs', 'catalogs.company_id', '=', 'companies.id')
            ->where('catalogs.status', Catalogs::CATALOG_PUBLISH)
            ->join('files', 'files.filesable_id', '=', 'catalogs.id')
            ->where('files.filesable_type', Catalogs::class)
            ->pluck('catalogs.id');
    }
}
