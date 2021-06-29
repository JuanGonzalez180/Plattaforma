<?php

namespace App\Http\Controllers\ApiControllers\category;

use JWTAuth;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiControllers\ApiController;

class CategoryListController extends ApiController
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
        $parents = Category::whereNull('parent_id')
            ->where('status', Category::CATEGORY_PUBLISH)
            ->orderBy('name','ASC')
            ->pluck('id');

        $parent_array = [];

        foreach($parents as $parent) {

            $childs = DB::select('call get_child_type_categoty("'.$parent.'")');

            if(count($childs) <= 1)
                continue;

            $parent_array[] = $childs;
        }

        return $parent_array;

    }
}
