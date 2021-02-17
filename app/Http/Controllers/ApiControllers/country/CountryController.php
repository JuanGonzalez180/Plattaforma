<?php

namespace App\Http\Controllers\ApiControllers\country;

use App\Models\Country;
use App\Http\Controllers\ApiControllers\ApiController;

class CountryController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $countries = Country::all();
        return $this->showAll($countries);
    }
}
