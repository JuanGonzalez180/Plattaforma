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
    
    public function show($id)
    {
        $company = Company::find($id);
        return view('company.show', compact('company'));
    }
}
