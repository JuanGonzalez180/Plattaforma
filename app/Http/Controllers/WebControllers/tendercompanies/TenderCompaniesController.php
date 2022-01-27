<?php

namespace App\Http\Controllers\WebControllers\tendercompanies;

use Illuminate\Http\Request;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TenderCompaniesController extends Controller
{
    public function index($tender_id)
    {
        $tenderCompanies = TendersCompanies::where('tender_id', $tender_id)
            ->get();

        return view('tendercompanies.index', compact('tenderCompanies'));
    }
    
    public function show($tender_company_id)
    {
        $tenderCompany = TendersCompanies::find($tender_company_id);

        $status = array(
            TendersCompanies::STATUS_EARRING ,
            TendersCompanies::STATUS_PARTICIPATING, 
            TendersCompanies::STATUS_REJECTED, 
            TendersCompanies::STATUS_PROCESS
        );
        
        return view('tendercompanies.show', compact(['tenderCompany','status']));
    }

    public function update(Request $request)
    {
        $tenderCompany = TendersCompanies::find($request->id);
        $tenderCompany->status = $request->status;
        $tenderCompany->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case TendersCompanies::STATUS_EARRING:
                //
                break;
            case TendersCompanies::STATUS_PARTICIPATING:
                //
                break;
            case TendersCompanies::STATUS_REJECTED:
                //
                break;
            case TendersCompanies::STATUS_PROCESS:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
    }
}
