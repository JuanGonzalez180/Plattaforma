<?php

namespace App\Http\Controllers\WebControllers\quoteEmailInvitation;

use App\Models\TemporalInvitationCompanyQuote;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotes;
use App\Models\Company;
use DataTables;

class quoteEmailInvitationController extends Controller
{
    public function getFullCompanyQuotesEmails()
    {
        $quotes     = $this->getQuotes();
        $companies  = $this->getCompanies();

        $order['CREATED_DESC']      =   'Registro mas reciente';
        $order['CREATED_ASC']       =   'Registro mas antiguo';
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A';

        return view('quotemailcompanies.index', compact(['quotes', 'companies', 'order']));
    }

    public function getQuotes()
    {
        return Quotes::select('quotes.id', 'quotes.name')
            ->distinct()
            ->join('temporal_invitation_companies_quote', 'temporal_invitation_companies_quote.quote_id', '=', 'quotes.id')
            ->orderBy('quotes.created_at', 'desc')
            ->get();
    }

    public function getCompanies()
    {
        return Company::select('companies.id', 'companies.name')
            ->distinct()
            ->join('quotes', 'quotes.company_id', '=', 'companies.id')
            ->join('temporal_invitation_companies_quote', 'temporal_invitation_companies_quote.quote_id', '=', 'quotes.id')
            ->get();
    }

    public function getQuotesInvitation(Request $request)
    {
        $order  = $request->size;
        $quote  = $request->quote;

        $quoteEmails = TemporalInvitationCompanyQuote::select('temporal_invitation_companies_quote.id', 'temporal_invitation_companies_quote.email', 'quotes.name as quote_name', 'companies.name as company_name', 'temporal_invitation_companies_quote.send', 'temporal_invitation_companies_quote.created_at as created_at');

        if ($quote != 'all')
            $quoteEmails = $quoteEmails->where('temporal_invitation_companies_quote.quote_id', '=', $quote);

        $quoteEmails = $quoteEmails->join('quotes', 'quotes.id', '=', 'quote_id')
            ->join('companies', 'companies.id', '=', 'quotes.company_id');

        if ($order == 'CREATED_DESC') {
            $quoteEmails = $quoteEmails->orderBy('temporal_invitation_companies_quote.created_at', 'desc');
        } else if ($order == 'CREATED_ASC') {
            $quoteEmails = $quoteEmails->orderBy('temporal_invitation_companies_quote.created_at', 'asc');
        } else if ($order == 'ALPHABETICAL_DESC') {
            $quoteEmails = $quoteEmails->orderBy('temporal_invitation_companies_quote.email', 'desc');
        } else if ($order == 'ALPHABETICAL_ASC') {
            $quoteEmails = $quoteEmails->orderBy('temporal_invitation_companies_quote.email', 'asc');
        }

        $quoteEmails = $quoteEmails->get();


        return DataTables::of($quoteEmails)
            ->addColumn('register_email', function (TemporalInvitationCompanyQuote $value) {
                return ($value->mailExists()) ? '<span class="badge badge-success">Registrado</span>' : '<span class="badge badge-danger">No Registrado</span>';
            })
            ->addColumn('date', function (TemporalInvitationCompanyQuote $value) {
                return $value->created_at->formatLocalized('%d %b %Y %H:%M %p') . "<br>" . "<span class='badge badge-light'>" . $value->created_at->diffForHumans() . "</span>";
            })
            ->editColumn('send', function (TemporalInvitationCompanyQuote $value) {
                return ($value->send) ? '<span class="badge badge-success">Si</span>' : '<span class="badge badge-danger">No</span>';
            })
            ->rawColumns(['id', 'email', 'quote_name', 'company_name', 'send', 'register_email', 'date'])
            ->toJson();
    }
}
