<?php

namespace App\Http\Controllers\ApiControllers\search;

use JWTAuth;
use App\Models\User;
use App\Models\Company;
use App\Models\Projects;
use App\Models\MetaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class SearchProjectsController extends ApiController
{   
    public $routeFile = 'public/';
    public $routeProjects = 'images/projects/';

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke()
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();
        if( $companyID && $user->userType() == 'oferta' ){
            // 
            // Filtros Búsquedas y demás
            $projects = Projects::all();

            foreach( $projects as $key => $project ){
                $project->user['url'] = $project->user->image ? url( 'storage/' . $project->user->image->url ) : null;
                /*unset($project->user['id']);
                unset($project->user['username']);
                unset($project->user['email']);
                unset($project->user['email_verified_at']);
                unset($project->user['verified']);
                unset($project->user['validated']);
                unset($project->user['admin']);
                unset($project->user['card_brand']);
                unset($project->user['card_last_four']);
                unset($project->user['trial_ends_at']);*/
            }
            return $this->showAllPaginate($projects);
        }
        return [];
    }
}