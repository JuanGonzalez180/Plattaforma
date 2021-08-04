<?php

namespace App\Http\Controllers\WebControllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

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
    
    public function show($id)
    {
        $company = Company::find($id);
        return view('company.show', compact('company'));
    }
}
