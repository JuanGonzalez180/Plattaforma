<?php

namespace App\Http\Controllers\WebControllers\company\providers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyProvidersController extends Controller
{
    public function index()
    {
        $status = [
            Company::COMPANY_CREATED,
            Company::COMPANY_APPROVED,
            Company::COMPANY_REJECTED
        ];

        return view('company.projects.index', compact('status'));
    }
}
