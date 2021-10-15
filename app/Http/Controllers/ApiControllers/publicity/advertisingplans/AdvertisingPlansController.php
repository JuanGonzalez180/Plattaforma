<?php

namespace App\Http\Controllers\ApiControllers\publicity\advertisingplans;

use Illuminate\Http\Request;
use App\Models\AdvertisingPlans;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class AdvertisingPlansController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $plans = AdvertisingPlans::all();
        return $this->showAllPaginate($plans);
    }
}
