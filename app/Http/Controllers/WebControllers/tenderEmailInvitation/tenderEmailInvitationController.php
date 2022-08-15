<?php

namespace App\Http\Controllers\WebControllers\tenderEmailInvitation;

use App\Models\TemporalInvitationCompany;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenders;
use App\Models\Company;
use DataTables;

class tenderEmailInvitationController extends Controller
{
    public function getFullCompanyTendersEmails()
    {
        $tenders    = $this->getTenders();
        $companies  = $this->getCompanies();

        $order['CREATED_DESC']      =   'Registro mas reciente';
        $order['CREATED_ASC']       =   'Registro mas antiguo';
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A';

        return view('tenderemailcompanies.index', compact(['tenders', 'companies', 'order']));
    }

    public function getTenders()
    {
        return Tenders::select('tenders.id', 'tenders.name')
            ->distinct()
            ->join('temporal_invitation_companies', 'temporal_invitation_companies.tender_id', '=', 'tenders.id')
            ->orderBy('tenders.created_at', 'desc')
            ->get();
    }

    public function getCompanies()
    {
        return Company::select('companies.id', 'companies.name')
            ->distinct()
            ->join('tenders', 'tenders.company_id', '=', 'companies.id')
            ->join('temporal_invitation_companies', 'temporal_invitation_companies.tender_id', '=', 'tenders.id')
            ->get();
    }

    public function getTendersInvitation(Request $request)
    {
        $order       = $request->size;
        $tender      = $request->tender;

        $tenderEmails = TemporalInvitationCompany::select('temporal_invitation_companies.id', 'temporal_invitation_companies.email', 'tenders.name as tender_name', 'companies.name as company_name', 'temporal_invitation_companies.send', 'temporal_invitation_companies.created_at as created_at');

        if($tender != 'all')
            $tenderEmails = $tenderEmails->where('temporal_invitation_companies.tender_id', '=', $tender);

        $tenderEmails = $tenderEmails->join('tenders', 'tenders.id', '=', 'tender_id')
            ->join('companies', 'companies.id', '=', 'tenders.company_id');

        if ($order == 'CREATED_DESC') {
            $tenderEmails = $tenderEmails->orderBy('temporal_invitation_companies.created_at', 'desc');
        } else if ($order == 'CREATED_ASC') {
            $tenderEmails = $tenderEmails->orderBy('temporal_invitation_companies.created_at', 'asc');
        } else if ($order == 'ALPHABETICAL_DESC') {
            $tenderEmails = $tenderEmails->orderBy('temporal_invitation_companies.email', 'desc');
        } else if ($order == 'ALPHABETICAL_ASC') {
            $tenderEmails = $tenderEmails->orderBy('temporal_invitation_companies.email', 'asc');
        }

        $tenderEmails = $tenderEmails->get();

        return DataTables::of($tenderEmails)
            ->addColumn('register_email', function (TemporalInvitationCompany $value) {
                return ($value->mailExists()) ? '<span class="badge badge-success">Registrado</span>' : '<span class="badge badge-danger">No Registrado</span>';
            })
            ->addColumn('date', function (TemporalInvitationCompany $value) {
                return $value->created_at->formatLocalized('%d %b %Y %H:%M %p') . "<br>" . "<span class='badge badge-light'>" . $value->created_at->diffForHumans() . "</span>";
            })
            ->editColumn('send', function (TemporalInvitationCompany $value) {
                return ($value->send) ? '<span class="badge badge-success">Si</span>' : '<span class="badge badge-danger">No</span>';
            })
            ->rawColumns(['id', 'email', 'tender_name', 'company_name', 'send', 'register_email', 'date'])
            ->toJson();
    }
}
