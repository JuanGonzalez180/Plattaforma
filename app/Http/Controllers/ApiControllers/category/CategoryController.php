<?php

namespace App\Http\Controllers\ApiControllers\category;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class CategoryController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $categories = Category::all();
        return $this->showAll($categories);
    }
}
