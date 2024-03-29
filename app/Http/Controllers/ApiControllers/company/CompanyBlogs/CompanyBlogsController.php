<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyBlogs;

use JWTAuth;
use App\Models\Blog;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Transformers\BlogTransformer;
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
        
        foreach ( $company->blogs as $key => $blog) {
            $blog->files;
        }

        return $this->showAllPaginate($company->blogs);
    }

    public function show( $slug, $id ) {

        $user = $this->validateUser();

        $blog = Blog::where('id', $id)
                        ->where('status',Blog::BLOG_PUBLISH)
                        ->first();

        if( !$id || !$blog ){
            $BlogError = [ 'blog' => 'Error, no se ha encontrado ningun blog' ];
            return $this->errorResponse( $BlogError, 500 );
        }

        $blogTransformer = new BlogTransformer();

        return $this->showOneData( $blogTransformer->transformDetail($blog), 200 );
    }
}