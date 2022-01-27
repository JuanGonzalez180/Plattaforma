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

        $status = array(
            'especificaciones-tecnicas',
            'en-construccion', 
        );

        return view('project.show', compact(['project','status']));
    }

    public function update(Request $request)
    {
        $project = Projects::find($request->id);
        $project->status = $request->status;
        $project->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case Projects::PROJECTS_ERASER:
                //
                break;
            case Projects::PROJECTS_PUBLISH:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
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
