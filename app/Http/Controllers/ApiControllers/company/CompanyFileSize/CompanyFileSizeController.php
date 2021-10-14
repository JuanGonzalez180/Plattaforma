<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyFileSize;

use JWTAuth;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyFileSizeController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        // Validamos TOKEN del usuario
        $user       = $this->validateUser();
        $companyID  = $user->companyId();
        $company    = Company::find($companyID);

        return $this->bitesToGigabite($company->fileSizeTotal());
        
    }

    public function bitesToGigabite($file_size)
    {
        return round(($file_size / pow(1024, 3)), 3);
    }
}
