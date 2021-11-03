<?php

namespace App\Http\Controllers\WebControllers\company;

use DataTables;
use App\Models\Type;
use App\Models\Company;
use App\Models\Addresses;
use Illuminate\Http\Request;
use App\Mail\ValidatedAccount;
use Illuminate\Validation\Rule;
use App\Mail\RejectedAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class CompanyController extends Controller
{
    public function index()
    {
        $types = Type::select('id','name')
            ->orderBy('name','asc')
            ->get();

        return view('company.index2', compact('types'));
    }

    public function getCompanyType($type)
    {
        $companies_a = Company::select('companies.*')
            ->where('companies.status','=',Company::COMPANY_CREATED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.name','=',$type)
            ->orderBy('companies.updated_at','desc')
            ->get();
            
        $companies_b = Company::select('companies.*')
            ->where('companies.status','<>',Company::COMPANY_CREATED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.name','=',$type)
            ->orderBy('companies.updated_at','desc')
            ->get();

        $companies = $companies_a->merge($companies_b);

        return view('company.index', compact('companies','type'));
    }

    public function edit($id)
    {
        $company = Company::find($id);
        return view('company.edit',compact('company'));
    }
    
    public function show($id)
    {
        $company = Company::find($id);
        return view('company.show', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name'          => ['required', Rule::unique('companies')->ignore($id) ],
            'nit'           => ['required', Rule::unique('companies')->ignore($id) ],
            'country_code'  => ['required']
        ];

        $this->validate( $request, $rules );


        $company                = Company::find($id);
        $company->name          = $request->name;
        $company->nit           = $request->nit;
        $company->country_code  = $request->country_code;

        $company->save();

        if(!is_null($request->address))
        {
            $address    = Addresses::where('addressable_id', $id)
                ->where('addressable_type',Company::class);

            if($address->exists())
            {
                $address->update(['address' => $request->address]);
            }
            else
            {
                $address = new Addresses;
                $address->addressable_id    = $id;
                $address->addressable_type  = Company::class;
                $address->address           = $request->address;
                $address->save();
            }
        }

        return redirect()->route('companies-type', ($company->type_entity->type->name == 'Demanda')? 'Demanda': 'Oferta')->with([
            'title' => "La compañia fue actulizada con exito",
        ]);
    }

    public function editStatus(Request $request){
        $company = Company::find($request->id);
        // Cambiamos el estado de la compañia
        $company->status = $request->status;
        $company->save();
        // Enviamos mensaje al correo del usuario
        if($request->status == Company::COMPANY_APPROVED)
            Mail::to($company->user->email)->send(new ValidatedAccount($company->user));

        if($request->status == Company::COMPANY_REJECTED)
            Mail::to($company->user->email)->send(new RejectedAccount($company->user));

        $type_company = ($company->type_company() == 'Oferta')? 'Oferta' : 'Demanda';

        return redirect()->route('companies-type', $type_company)->with([
            'status'    => 'edit',
            'title'     => 'La compañia'
        ]);
    }

    public function getTypeCompanies(Request $request)
    {
        $type_id  = $request->type_id;

        $companies_a = Company::select('companies.*')
            ->where('companies.status','=',Company::COMPANY_CREATED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.id','=',$type_id)
            ->orderBy('companies.updated_at','desc')
            ->get();

        $companies_b = Company::select('companies.*')
            ->where('companies.status','<>',Company::COMPANY_CREATED)
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.id','=',$type_id)
            ->orderBy('companies.updated_at','desc')
            ->get();

        $companies = $companies_a->merge($companies_b);

        return DataTables::of($companies)
            ->addColumn('type_entity','company.datatables.entity')
            ->addColumn('status','company.datatables.status')
            ->addColumn('action','company.datatables.action')
            ->rawColumns(['actions','status','type_entity'])
            ->toJson();
    }
}
