<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Blog;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyBlogsController extends ApiController
{
    //
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index( $slug )
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        // Traer Blogs de la compañía
        $company->blogs = $company->blogs
                        ->where('status', Blog::BLOG_PUBLISH)
                        ->sortBy([ ['updated_at', 'desc'] ]);
        
        return $this->showAllPaginate($company->blogs);
    }

    public function detail(Request $request, $slug)
    {
        $user = $this->validateUser();

        $name = $request->name;

        $blogs = Blog::select('blogs.*')
            ->where('blogs.status','=',Blog::BLOG_PUBLISH)
            ->join('companies','companies.id','=','blogs.company_id')
            ->where('companies.slug','=',$slug)
            ->where(strtolower('blogs.name'),'LIKE','%'.strtolower($name).'%')
            ->orderBy('blogs.updated_at', 'desc')
            ->get(); 

        if( !$blogs ){
            $blogsError = [ 'blogs' => 'Error, no se ha encontrado ningun blog' ];
            return $this->errorResponse( $blogsError, 500 );
        }

        return $this->showOneTransformNormal($blogs, 200);
    }
}
