<?php

namespace App\Http\Controllers\WebControllers\quote;

use DataTables;
use App\Models\Quotes;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\QuotesVersions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class QuoteController extends Controller
{

    public function getFullQuotes()
    {
        $companies = Quotes::select('companies.id', 'companies.name')
            ->join('companies', 'companies.id', '=', 'quotes.company_id')
            ->orderBy('companies.name', 'asc')
            ->distinct()
            ->get();

        $quoteStatus[QuotesVersions::QUOTATION_CREATED]    =   QuotesVersions::QUOTATION_CREATED;
        $quoteStatus[QuotesVersions::QUOTATION_PUBLISH]    =   QuotesVersions::QUOTATION_PUBLISH;
        $quoteStatus[QuotesVersions::QUOTATION_FINISHED]   =   QuotesVersions::QUOTATION_FINISHED;

        $order['CREATED_DESC']      =   'fecha de cierre mas reciente';
        $order['CREATED_ASC']       =   'fecha de cierre mas antigua';
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A';

        return view('quote.general.showAll', compact(['companies', 'quoteStatus', 'order']));
    }

    public function getQuotes(Request $request)
    {
        $company        = $request->company;
        $status         = $request->status;
        $order          = $request->orders;

        $quotes  = Quotes::select('quotes.*');

        if ($company != 'all') {
            $quotes = $quotes->where('company_id', '=', $company);
        }

        $quotes = $quotes->get();

        $quotes->map(function ($item, $key) {
            return $item->version_status = $item->quotesVersionLast()->status;
        });

        if ($status != 'all') {
            $quotes = collect($quotes)->where('version_status', $status);
        }

        if ($order == 'CREATED_DESC') {
            $quotes = collect($quotes)->sortBy([['created_at', 'desc']]);
        } else if ($order == 'CREATED_ASC') {
            $quotes = collect($quotes)->sortBy([['created_at', 'asc']]);
        } else if ($order == 'ALPHABETICAL_DESC') {
            $quotes = collect($quotes)->sortBy([['name', 'asc']]);
        } else if ($order == 'ALPHABETICAL_ASC') {
            $quotes = collect($quotes)->sortBy([['name', 'asc']]);
        }

        return DataTables::of($quotes)
            ->editColumn('company_id', function (Quotes $value) {
                return strtoupper($value->company->name);
            })
            ->editColumn('version_status', function (Quotes $value) {

                $status = $value->version_status;

                switch ($value->version_status) {
                    case QuotesVersions::QUOTATION_CREATED:
                        $status = "<span class='badge badge-pill badge-secondary'>" . QuotesVersions::QUOTATION_CREATED . "</span>";
                        break;
                    case QuotesVersions::QUOTATION_PUBLISH:
                        $status = "<span class='badge badge-success'>" . QuotesVersions::QUOTATION_PUBLISH . "</span>";
                        break;
                    case QuotesVersions::QUOTATION_FINISHED:
                        $status = "<span class='badge badge-pill badge-secondary'>" . QuotesVersions::QUOTATION_FINISHED . "</span>";
                        break;
                    default:
                        $status = "<span class='badge badge-pill badge-secondary'>" . $value->version_status . "</span>";
                }

                return $status;
            })
            ->editColumn('name', function (Quotes $value) {
                return $value->quotesVersionLast()->adenda;
            })
            ->editColumn('user_id', function (Quotes $value) {
                return $value->user->fullName();
            })
            ->addColumn('action', function (Quotes $value) {
                $action = '<div class="btn-group" role="group" aria-label="Button group with nested dropdown">';
                $action = $action . '<a type="button" href="' .  url('/cotizaciones/' . $value->id) . '" class="btn btn-success btn-sm"><i class="far fa-eye"></i></a>';
                $action = $action . '<div class="btn-group" role="group">';
                $action = $action . '<button id="btnGroupDrop1" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span></button>';
                $action = $action . '<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
                //Compañias Licitantes
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('quote-companies-id', $value->id) . '">Compañias cotizantes &nbsp;<span class="badge badge-primary">' . count($value->quoteCompanies) . '</span></a>';
                //Muro de consultas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('query.quotes.class.id', $value->id) . '">Muro de consultas &nbsp;<span class="badge badge-primary">' . count($value->querywalls) . '</span></a>';
                $action = $action . '</div>';
                $action = $action . '</div>';
                $action = $action . '</div>';

                return $action;
            })
            ->rawColumns(['company_id', 'action', 'version_status','name'])
            ->toJson();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type, $id)
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $quote = Quotes::find($id);
        return view('quote.general.show', compact('quote'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
