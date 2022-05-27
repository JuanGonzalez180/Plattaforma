<?php

namespace App\Http\Controllers\WebControllers\tenderEmailCompanies;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenders;
use App\Models\Company;

class TenderEmailCompaniesController extends Controller
{
    public function getFullCompanyTendersEmails()
    {
        $tenders    = $this->getTenders();
        $companies  = $this->getCompanies();

        return view('tenderemailcompanies.index', compact(['tenders', 'companies']));
    }

    public function getTenders()
    {
        return Tenders::select('tenders.id','tenders.name')
            ->distinct()
            ->join('temporal_invitation_companies','temporal_invitation_companies.tender_id','=','tenders.id')
            ->get();
    }

    public function getCompanies()
    {
        return Company::select('companies.id','companies.name')
            ->distinct()
            ->join('tenders','tenders.company_id','=','companies.id')
            ->join('temporal_invitation_companies','temporal_invitation_companies.tender_id','=','tenders.id')
            ->get(); 
    }
}
