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
        $visible    = Projects::PROJECTS_VISIBLE;

        return view('project.index', compact('projects','visible'));
    }

    public function show($id)
    {
        $project    = Projects::find($id);
        return view('project.show', compact('project'));
    }

    public function editVisible(Request $request)
    {
        $project    = Projects::find($request->id); 
        $visible    = ($project->visible == Projects::PROJECTS_VISIBLE) ? Projects::PROJECTS_VISIBLE_NO : Projects::PROJECTS_VISIBLE;
        $project->visible = $visible;
        $project->save();

        return redirect()->route('project-company-id', $project->company->id )->with('success', 'Se ha cambiado el estado del proyecto');
    }
}
