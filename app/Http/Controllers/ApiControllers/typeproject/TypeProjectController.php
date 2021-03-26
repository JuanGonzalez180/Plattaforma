<?php

namespace App\Http\Controllers\ApiControllers\typeproject;

use App\Models\TypeProject;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class TypeProjectController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $typeProjects = TypeProject::all();
        return $this->showAll($typeProjects);
    }
}
