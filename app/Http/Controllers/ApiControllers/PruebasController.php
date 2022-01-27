<?php

namespace App\Http\Controllers\ApiControllers;

use Carbon\Carbon;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use App\Http\Controllers\Controller;

class PruebasController extends Controller
{
    public function index()
    {
        // $tenders = Tenders::all();

        // foreach($tenders as $tender) {

        //     $statusValidate = ($tender->tendersVersionLast()->status == TendersVersions::LICITACION_PUBLISH);
        //     $dateValidate   = ($tender->tendersVersionLast()->date   == Carbon::now()->format('Y-m-d'));
        //     $hourValidate   = ($tender->tendersVersionLast()->hour   == Carbon::now()->format('G:i'));

        //     if($statusValidate && $dateValidate && $hourValidate) {
        //         var_dump('entro');
        //         $tender->tendersVersionLast()->status = TendersVersions::LICITACION_CLOSED;
        //         $tender->tendersVersionLast()->save();
        //     };
        // }

    }
}
