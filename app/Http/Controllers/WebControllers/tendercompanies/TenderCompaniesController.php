<?php

namespace App\Http\Controllers\WebControllers\tendercompanies;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TendersCompanies;

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
        
        return view('tendercompanies.show', compact('tenderCompany'));
    }
}
