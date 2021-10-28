<?php

namespace App\Http\Controllers\WebControllers\publicity\manageadvertising;

use DataTables;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Advertisings;
use App\Http\Controllers\Controller;
use App\Models\RegistrationPayments;

class ManageAdvertisingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->getCompanies();

        return view('publicity.manageadvertising.index', compact('companies'));
    }

    public function getCompanies()
    {
        return RegistrationPayments::select('companies.id', 'companies.name')
            ->where('paymentsable_type', Advertisings::class)
            ->join('companies', 'companies.id', '=', 'registration_payments.company_id')
            ->distinct('companies.id')
            ->where('companies.status', Company::COMPANY_APPROVED)
            ->get();
    }

    public function getAdvertisingCompanies(Request $request)
    {
        $company = $request->company_id;

        $advertisingList = Advertisings::select('advertisings.*');

        if ($company != 'all') {
            $advertisingList = $advertisingList->join('registration_payments', function ($join) {
                $join->on('advertisings.id', '=', 'registration_payments.paymentsable_id')
                    ->where('registration_payments.paymentsable_type', '=',  Advertisings::class);
            })->where('registration_payments.company_id', $company);
        };

        $advertisingList = $advertisingList->orderBy('start_date', 'desc');

        return DataTables::of($advertisingList)
            ->editColumn('name', function (Advertisings $value) {
                $message = '<cite title="Source Title">' . $value->name . '</cite><br>';
                $message = $message . '<span class="badge badge-primary">' . ($value->type_publicity_detail())['type'] . '</span> | <b>' . ($value->type_publicity_detail())['name'] . '</b><br>';
                return $message;
            })
            ->addColumn('plan', function (Advertisings $value) {
                $text = '<cite title="Source Title">' . $value->Plan->name . '</cite><br>';
                $text = $text . '<b>Dias | </b>' . $value->Plan->days . '<br>';
                $text = $text . '<b>Precio | </b><span class="badge badge-success">$' . $value->Plan->price . '</span><br>';

                $status = '';
                if ($value->payments->status == RegistrationPayments::REGISTRATION_PENDING) {
                    $status = '<span class="badge badge-warning"><i class="far fa-clock"></i> ' . RegistrationPayments::REGISTRATION_PENDING . '</span>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_APPROVED) {
                    $status = '<span class="badge badge-success"><i class="fas fa-check"></i> ' . RegistrationPayments::REGISTRATION_APPROVED . '</span>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_REJECTED) {
                    $status = '<span class="badge badge-danger"><i class="fas fa-times"></i> ' . RegistrationPayments::REGISTRATION_REJECTED . '</span>';
                }

                $text = $text . '<b>Estado de pago | </b>' . $status . '<br>';
                return $text;
            })
            ->addColumn('status', function (Advertisings $value) {

                if ($value->payments->status == RegistrationPayments::REGISTRATION_PENDING) {
                    $status = '<div class="alert alert-primary" role="alert">
                                    <i class="fas fa-info-circle"></i>&nbsp; El pago sigue pendiente.
                                </div>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_REJECTED) {
                    $status = '<div class="alert alert-danger" role="alert">
                                    <i class="fas fa-times"></i>&nbsp; El pago ha sido rechazado
                                </div>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_APPROVED && $value->status == Advertisings::STATUS_ADMIN_CREATED) {
                    $status = '<span class="badge bg-light text-dark"><i class="far fa-clock"></i> Revision</span>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_APPROVED && $value->status == Advertisings::STATUS_ADMIN_APPROVED) {
                    $status = '<span class="badge badge-success"><i class="fas fa-check"></i> Aprobada</span>';
                } else if ($value->payments->status == RegistrationPayments::REGISTRATION_APPROVED && $value->status == Advertisings::STATUS_ADMIN_REJECTED) {
                    $status = '<span class="badge badge-danger"><i class="fas fa-times"></i> Rechazada</span>';
                }

                return $status;
            })
            ->addColumn('action', function (Advertisings $value) {
                return '<a type="button" href="' . route('manage_publicity_plan.show', $value->id) . '" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span></a>';
            })
            ->rawColumns(['name', 'plan', 'status', 'action'])
            ->toJson();
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
        $advertising    = Advertisings::find($id);

        $status         = [
            Advertisings::STATUS_ADMIN_CREATED,
            Advertisings::STATUS_ADMIN_APPROVED,
            Advertisings::STATUS_ADMIN_REJECTED
        ];

        $status_payment = [
            RegistrationPayments::REGISTRATION_PENDING,
            RegistrationPayments::REGISTRATION_APPROVED,
            RegistrationPayments::REGISTRATION_REJECTED
        ];

        // $status_payment = RegistrationPayments::REGISTRATION_PENDING;


        return view('publicity.manageadvertising.show', compact('advertising', 'status', 'status_payment'));
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
