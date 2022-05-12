<?php

namespace App\Http\Controllers\WebControllers\tender;

use DataTables;
use App\Models\Tenders;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Mail\SendDeclinedTenderCompany;

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

    public function updateStatusDecline(Request $request)
    {
        $tenderVersionLast = TendersVersions::find($request->id);

        DB::beginTransaction();

        $tenderVersionLast->status = TendersVersions::LICITACION_DECLINED;

        try{
            $tenderVersionLast->save();
            DB::commit();
            //Informar por correo a los participantes que se ha declinado la licitación.
            $tencompanies  = $tenderVersionLast->tenders->tenderCompanies;

            foreach ($tencompanies as $tencompany)
                Mail::to($tencompany->company->user->email)->send(new SendDeclinedTenderCompany($tenderVersionLast->tenders->name, $tencompany->company->name));

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('tender-company-id', ['company',$tenderVersionLast->tenders->company->id] )->with('danger', 'Error, no se ha podido gestionar la solicitud de la licitación');
        }

        return redirect()->route('tender-company-id', ['company',$tenderVersionLast->tenders->company->id] )->with('success', 'Se ha declinado la licitación');
    }

    public function getFullTenders()
    {
        $companies = Tenders::select('companies.id','companies.name')
            ->join('companies','companies.id','=','tenders.company_id')
            ->orderBy('companies.name','asc')
            ->distinct()
            ->get();

        $tenderStatus = [
            TendersVersions::LICITACION_CREATED, 
            TendersVersions::LICITACION_PUBLISH, 
            TendersVersions::LICITACION_CLOSED, 
            TendersVersions::LICITACION_FINISHED, 
            TendersVersions::LICITACION_DISABLED
        ];


        $order['CREATED_DESC']      =   'fecha de cierre mas reciente';
        $order['CREATED_ASC']       =   'fecha de cierre mas antigua';
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A'; 

        return view('tender.showAll', compact(['companies', 'tenderStatus', 'order']));
    }


    public function getTenders(Request $request)
    {
        // $company        = $request->company;
        // $status         = $request->status;
        // $order          = $request->order;

        $tenders  = Tenders::select('tenders.*')->get();


        
        return DataTables::of($tenders)
        ->editColumn('company_id', function (Tenders $value) {
            return $value->company->name;
        })
        ->editColumn('user_id', function (Tenders $value) {
            return $value->user->name;
        })
        ->toJson();
    }
}
