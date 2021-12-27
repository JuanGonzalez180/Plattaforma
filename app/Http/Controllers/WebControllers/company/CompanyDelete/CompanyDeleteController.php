<?php

namespace App\Http\Controllers\WebControllers\company\CompanyDelete;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyDeleteController extends Controller
{
    public function __invoke($id)
    {
        var_dump('hola mundo');
    }
}
