<?php

namespace App\Http\Controllers\WebControllers\quotecompanies;

use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class QuoteCompaniesController extends Controller
{
    public function index($quote_id)
    {
        $quote_company = QuotesCompanies::where('quotes_id', $quote_id)
            ->get();

        return view('quotecompanies.index', compact('quote_company'));
    }

    public function show($quote_company_id)
    {
        $quoteCompany = QuotesCompanies::find($quote_company_id);

        $status = array(
            QuotesCompanies::STATUS_EARRING ,
            QuotesCompanies::STATUS_PARTICIPATING, 
            QuotesCompanies::STATUS_REJECTED, 
            QuotesCompanies::STATUS_PROCESS
        );
        
        return view('quotecompanies.show', compact(['quoteCompany','status']));
    }

    public function update(Request $request)
    {
        $quoteCompany = QuotesCompanies::find($request->id);
        $quoteCompany->status = $request->status;
        $quoteCompany->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case QuotesCompanies::STATUS_EARRING:
                //
                break;
            case QuotesCompanies::STATUS_PARTICIPATING:
                //
                break;
            case QuotesCompanies::STATUS_REJECTED:
                //
                break;
            case QuotesCompanies::STATUS_PROCESS:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
    }
}
