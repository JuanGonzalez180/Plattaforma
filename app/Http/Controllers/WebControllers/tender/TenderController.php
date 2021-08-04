<?php

namespace App\Http\Controllers\WebControllers\tender;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projects;
use App\Models\Tenders;

class TenderController extends Controller
{
    public function index($type, $id)
    {
        $tenders = ($type == 'company') ? Tenders::where('company_id',$id) : Tenders::where('project_id',$id);
        $tenders = $tenders->orderBy('created_at','asc')->get();
            
        return view('tender.index', compact('tenders'));
    }

    public function show($id)
    {
        $tender = Tenders::find($id);

        return view('tender.show', compact('tender'));
    }
    
}
