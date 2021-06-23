<?php

namespace App\Http\Controllers\ApiControllers\typeproject;

use JWTAuth;
use App\Models\User;
use App\Models\TypeProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class TypeProjectListController extends ApiController
{
    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        $parents = TypeProject::whereNull('parent_id')
            ->where('status', TypeProject::TYPEPROJECT_PUBLISH)
            ->pluck('id');

        $parent_array = [];

        foreach($parents as $parent) {
            $parent_array[] = DB::select('call get_child_type_project("'.$parent.'")');
        }

        return $parent_array;

    }
}
