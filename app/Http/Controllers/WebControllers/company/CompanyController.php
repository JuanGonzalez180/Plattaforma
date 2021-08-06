<?php

namespace App\Http\Controllers\WebControllers\company;

use App\Models\Company;
use App\Models\Addresses;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::query()->get();
        return view('company.index', compact('companies'));
    }

    public function getCompanyType($type)
    {
        $companies = Company::select('companies.*')
            ->join('types_entities','types_entities.id','=','companies.type_entity_id')
            ->join('types','types.id','=','types_entities.type_id')
            ->where('types.name','=',$type)
            ->get();

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
}
