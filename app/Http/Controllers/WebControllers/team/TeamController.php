<?php

namespace App\Http\Controllers\WebControllers\team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    public function index($company_id)
    {
        $teams_a = Team::where('company_id', $company_id)
            ->where('status','=',Team::TEAM_PENDING)  
            ->orderBy('updated_at','desc')  
            ->get();
            
        $teams_b = Team::where('company_id', $company_id)
            ->where('status','<>',Team::TEAM_PENDING)    
            ->orderBy('updated_at','desc')  
            ->get();

        $teams  = $teams_a->merge($teams_b);

        
        return view('team.index', compact('teams'));
    }

    public function show($id)
    {
        $team = Team::find($id);

        return view('team.show', compact('team'));
    }

    public function editStatus(Request $request)
    {
        $team = Team::find($request->id);
        // Cambiamos el estado del usuario del equipo
        $team->status = $request->status;
        $team->save();

        return redirect()->route('teams-company-id', $team->company_id)->with([
            'status'    => 'edit',
            'title'     => 'El usuario'
        ]);
    }
}
