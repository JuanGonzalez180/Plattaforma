<?php

namespace App\Http\Controllers\WebControllers\tender;

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
            // Informar por correo a los participantes que se ha declinado la licitación.
            $companies  = $this->getCompanyTenders($id);
            foreach ($companies as $company)
                Mail::to('cris10x@hotmail.com')->send(new SendDeclinedTenderCompany($tender->name, $company->name));

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('tender-company-id', ['company',$tenderVersionLast->tenders->company->id] )->with('danger', 'Error, no se ha podido gestionar la solicitud de la licitación');
        }

        return redirect()->route('tender-company-id', ['company',$tenderVersionLast->tenders->company->id] )->with('success', 'Se ha declinado la licitación');
    }
    
}
