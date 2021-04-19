<?php

namespace App\Http\Controllers\ApiControllers\categoryservices;

use App\Models\CategoryService;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class CategoryServicesController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $categories = CategoryService::all();
        return $this->showAll($categories);
    }
}
