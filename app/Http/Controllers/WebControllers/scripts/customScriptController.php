<?php

namespace App\Http\Controllers\WebControllers\scripts;

use App\Models\Company;
use App\Models\Addresses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class customScriptController extends Controller
{
    public function home()
    {
        $this->assignCompanyLocation();
    }

    public function assignCompanyLocation()
    {
        $companies  = Company::all();

        foreach ($companies as $key => $company)
        {
            var_dump('id: '.$company->id);
            echo('<pre>');
            var_dump('compañia: '.$company->name);
            echo('<pre>');
            var_dump('ubicación: '.$company->name);
            echo('<pre>');
            var_dump(!isset($company->address)? 'no existe' : 'existe');
            echo('<pre>');
            
            // $addressExiste = !isset($company->address)? true : false;

            // if($addressExiste)
            // {
            //     $address = new Addresses();
            //     $address->addressable_id    = $company->id;
            //     $address->addressable_type  = Company::class;
            //     $address->address           = 'Panama';
            //     $address->latitud           = '8.9814453';
            //     $address->longitud          = '-79.5188013';
            //     $address->save();
            // }

        }
    }
}

