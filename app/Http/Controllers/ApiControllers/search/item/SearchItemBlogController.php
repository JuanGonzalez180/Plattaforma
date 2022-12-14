<?php

namespace App\Http\Controllers\ApiControllers\search\item;

use JWTAuth;
use App\Models\Type;
use App\Models\Blog;
use App\Models\Company;
use App\Models\TypesEntity;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchItemBlogController extends ApiController
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
        // Palabra clave de busqueda de la publicación.
        $search         = !isset($request->search) ? null : $request->search;
        // Tipo de compañia
        $type_entity    = ($request->type_entity == 'all') ? null : $request->type_entity;

        // Tipo de usuario
        $type_user = ($this->validateUser())->userType();
        
        $type_company = ($type_user == 'demanda')? 'oferta' : 'demanda';

        //Retorna una lista activa de las compañia por tipo de comunidad.
        $companiesActive = $this->getCompaniesActive($type_company, $type_entity);

        //Retorna una lista de las publicacioens activas
        $blogs = $this->getBlogsActive($companiesActive);

        if (!is_null($search)) {
            $blogs = $this->getBlogSearchName($blogs, $search);
        }

        $blogs = Blog::whereIn('id', $blogs)
            ->orderBy('name', 'asc')
            ->get();

        return $this->showAllPaginate($blogs);       
    }

    public function getBlogSearchName($blogs, $search)
    {
        //BUSCA POR EL NOMBRE DE LA PUBLICACIÓN
        $blogName       = $this->getBlogName($blogs, $search);
        //BUSCA POR EL NOMBRE DE LA COMPAÑIA
        $companyName    = $this->getCompanyName($blogs, $search);

        $blogs = array_unique(Arr::collapse([
            $blogName,
            $companyName
        ]));

        return $blogs;
    }

    public function getBlogName($Blog, $name)
    {
        return Blog::whereIn('id', $Blog)
            ->where(strtolower('name'), 'LIKE', '%' . strtolower($name) . '%')
            ->pluck('id');
    }

    public function getCompanyName($Blog, $name)
    {
        return Blog::whereIn('blogs.id', $Blog)
            ->join('companies', 'companies.id', '=', 'blogs.company_id')
            ->where(strtolower('companies.name'), 'LIKE', '%' . strtolower($name) . '%')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->pluck('blogs.id');
    }

    public function getBlogsActive($companiesActive)
    {
        return Blog::whereIn('company_id', $companiesActive)
            ->where('status', Blog::BLOG_PUBLISH)
            ->pluck('id');
    }

    public function getCompaniesActive($type_company, $type_entity)
    {
        $companies = Type::where('types.slug', $type_company)
            ->join('types_entities', 'types_entities.type_id', '=', 'types.id');

        if (!is_null($type_entity))
            $companies = $companies->where('types_entities.id', $type_entity);


        $companies = $companies->where('types_entities.status', TypesEntity::ENTITY_PUBLISH)
            ->join('companies', 'companies.type_entity_id', '=', 'types_entities.id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->pluck('companies.id');

        return $companies;
    }
}
