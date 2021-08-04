<?php

namespace App\Http\Controllers\WebControllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Projects;

class ProjectController extends Controller
{
    public function index($id)
    {
        $projects   = Projects::where('company_id', $id)->get();

        return view('project.index', compact('projects'));
    }

    public function show($id)
    {
        $project    = Projects::find($id);

        return view('project.show', compact('project'));
    }
}
