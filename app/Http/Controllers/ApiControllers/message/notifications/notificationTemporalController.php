<?php

namespace App\Http\Controllers\ApiControllers\message\notifications;

use JWTAuth;
use App\Models\User;
use App\Models\Quotes;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use App\Models\TendersCompanies;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class notificationTemporalController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function __invoke(Request $request)
    {
        $user  = User::find($this->validateUser()->id);

        //Busqueda por nombre licitación o cotización.
        $search         = !isset($request->search) ? null : $request->search;

        if($user->userType() == 'demanda')
        {
            $value = [
                "tender"    => $this->getTenders($user, $search),
                "quote"     => $this->getQuotes($user, $search)
            ];

        }
        elseif($user->userType() == 'oferta')
        {
            $value = [
                "tender"    => $this->getTenderParticipate($user, $search),
                "quote"     => $this->getQuoteParticipate($user, $search)
            ];
        }

        return $value;
    }

    public function getTenders($user, $search)
    {
        $isAdmin = $user->isAdminFrontEnd();
    

        $tenders = Tenders::where('company_id', $user->companyId());
        
        if(!$isAdmin)
            $tenders = $tenders->where('user_id', $user->id);

        if(!is_null($search))
            $tenders = $tenders->where(strtolower('name'), 'LIKE', '%' . strtolower($search) . '%');

        $tenders = $tenders->orderBy('created_at','desc')->get();

        return $this->showAllTransformer($tenders);
    }

    public function getQuotes($user, $search)
    {
        $isAdmin = $user->isAdminFrontEnd();
    

        $quotes = Quotes::where('company_id', $user->companyId());
        
        if(!$isAdmin)
            $quotes = $quotes->where('user_id', $user->id);

        if(!is_null($search))
            $quotes = $quotes->where(strtolower('name'), 'LIKE', '%' . strtolower($search) . '%');

        $quotes = $quotes->orderBy('created_at','desc')->get();

        return $this->showAllTransformer($quotes);

    }

    public function getTenderParticipate($user, $search)
    {
        $isAdmin = $user->isAdminFrontEnd();

        $tenderCompanies =  TendersCompanies::where('company_id', $user->companyId());

        if(!$isAdmin)
            $tenderCompanies =  $tenderCompanies->where('user_company_id', $user->id);

        $tenderCompanies = $tenderCompanies->where('status', TendersCompanies::STATUS_PARTICIPATING)
            ->pluck('tender_id');


        $tender = Tenders::whereIn('id', $tenderCompanies);

        if(!is_null($search))
            $tender = $tender->where(strtolower('name'), 'LIKE', '%' . strtolower($search) . '%');


        $tender = $tender->orderBy('created_at','desc')->get();

        return $this->showAllTransformer($tender);
    }

    public function getQuoteParticipate($user, $search)
    {
        $isAdmin = $user->isAdminFrontEnd();

        $quoteCompanies =  QuotesCompanies::where('company_id', $user->companyId());

        if(!$isAdmin)
            $quoteCompanies =  $quoteCompanies->where('user_company_id', $user->id);

        $quoteCompanies = $quoteCompanies->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->pluck('quotes_id');

        $quotes = Quotes::whereIn('id', $quoteCompanies);

        if(!is_null($search))
            $quotes = $quotes->where(strtolower('name'), 'LIKE', '%' . strtolower($search) . '%');

        $quotes = $quotes->orderBy('created_at','desc')->get();

        return $this->showAllTransformer($quotes);
    }
}
