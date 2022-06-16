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
                Mail::to(trim($tencompany->company->user->email))->send(new SendDeclinedTenderCompany($tenderVersionLast->tenders->name, $tencompany->company->name));

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

        $tenderStatus[TendersVersions::LICITACION_CREATED]  =   TendersVersions::LICITACION_CREATED;
        $tenderStatus[TendersVersions::LICITACION_PUBLISH]  =   TendersVersions::LICITACION_PUBLISH;
        $tenderStatus[TendersVersions::LICITACION_CLOSED]   =   'En Evaluación';
        $tenderStatus[TendersVersions::LICITACION_FINISHED] =   'Adjudicada'; 
        $tenderStatus[TendersVersions::LICITACION_DISABLED] =   'Suspendida'; 
        $tenderStatus[TendersVersions::LICITACION_DESERTED] =   TendersVersions::LICITACION_DESERTED; 


        $order['CREATED_DESC']      =   'fecha de cierre mas reciente';
        $order['CREATED_ASC']       =   'fecha de cierre mas antigua';
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A'; 

        return view('tender.showAll', compact(['companies', 'tenderStatus', 'order']));
    }


    public function getTenders(Request $request)
    {
        $company        = $request->company;
        $status         = $request->status;
        $order          = $request->orders;

        $tenders  = Tenders::select('tenders.*');

        if($company != 'all')
        {
            $tenders = $tenders->where('company_id','=',$company);
        }

        $tenders = $tenders->get();

        $tenders->map(function ($item, $key) {
            return $item->version_status = $item->tendersVersionLast()->status;
        });

        if($status != 'all')
        {
            $tenders = collect($tenders)->where('version_status', $status);
        }

        if($order == 'CREATED_DESC')
        {
            $tenders = collect($tenders)->sortBy([['created_at', 'desc']]);
        }
        else if($order == 'CREATED_ASC')
        {
            $tenders = collect($tenders)->sortBy([['created_at', 'asc']]);
        }
        else if($order == 'ALPHABETICAL_DESC')
        {
            $tenders = collect($tenders)->sortBy([['name', 'asc']]);
        }
        else if($order == 'ALPHABETICAL_ASC')
        {
            $tenders = collect($tenders)->sortBy([['name', 'asc']]);
        }

        return DataTables::of($tenders)
            ->editColumn('company_id', function(Tenders $value){
                return strtoupper($value->company->name);
            })
            ->editColumn('version_status', function(Tenders $value){

                $status = $value->version_status;

                switch ($value->version_status) {
                    case TendersVersions::LICITACION_CREATED:
                        $status = "<span class='badge badge-success'>".TendersVersions::LICITACION_PUBLISH."</span>";
                        break;
                    case TendersVersions::LICITACION_PUBLISH:
                        $status = "<span class='badge badge-success'>".TendersVersions::LICITACION_PUBLISH."</span>";
                        break;
                    case TendersVersions::LICITACION_CLOSED:
                        $status = "<span class='badge badge-warning'>En Evaluación</span>";
                        break;
                    case TendersVersions::LICITACION_FINISHED:
                        $status = "<span class='badge badge-pill badge-secondary'>Adjudicada</span>";
                        break;
                    case TendersVersions::LICITACION_DISABLED:
                        $status = "<span class='badge badge-pill badge-secondary'>Suspendida</span>";
                        break;
                    case TendersVersions::LICITACION_DESERTED:
                        $status = "<span class='badge badge-pill badge-secondary'>".TendersVersions::LICITACION_DESERTED."</span>";
                        break;
                    default:
                        $status = "<span class='badge badge-pill badge-secondary'>".$value->version_status."</span>";
                }

                return $status;
            })
            ->editColumn('user_id', function(Tenders $value){
                return $value->user->fullName();
            })
            ->addColumn('action', function (Tenders $value) {
                $action = '<div class="btn-group" role="group" aria-label="Button group with nested dropdown">';
                $action = $action . '<a type="button" href="' .  url('/licitaciones/'.$value->id) . '" class="btn btn-success btn-sm"><i class="far fa-eye"></i></a>';
                $action = $action . '<div class="btn-group" role="group">';
                $action = $action . '<button id="btnGroupDrop1" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span></button>';
                $action = $action . '<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
                //Compañias Licitantes
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('tender-companies-id', $value->id ) . '">Compañias Licitantes &nbsp;<span class="badge badge-primary">' . count($value->tenderCompanies) . '</span></a>';
                //Muro de consultas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('query.class.id', $value->id ) . '">Muro de consultas &nbsp;<span class="badge badge-primary">' . count($value->querywalls) . '</span></a>';
                $action = $action . '</div>';
                $action = $action . '</div>';
                $action = $action . '</div>';

                return $action;

            })
            ->rawColumns(['company_id','action','version_status'])
            ->toJson();


        
        // return DataTables::of($tenders)
        // ->editColumn('company_id', function (Tenders $value) {
        //     return $value->company->name;
        // })
        // ->editColumn('user_id', function (Tenders $value) {
        //     return $value->user->name;
        // })
        // ->toJson();
    }
}
