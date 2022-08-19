<?php

namespace App\Http\Controllers\ApiControllers\company\CompanyQuotes;

use JWTAuth;
use App\Models\Quotes;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyQuotesTransactController extends ApiController
{

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function postComparate(Request $request)
    {
        $user           = $this->validateUser();
        $companies_id   = $request->companies_id;

        $id_array = [];

        foreach($companies_id as $company_id){
            $id_array[] = $company_id['id'];
        }

        $quotesCompanies = QuotesCompanies::whereIn('id', $id_array)
            ->get();

        $transformer = QuotesCompanies::TRANSFORMER_QUOTE_COMPANY_SELECTED;

        return $this->showAllPaginateSetTransformer($quotesCompanies, $transformer);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($slug, int $id)
    {
        $user = $this->validateUser();
        //verifica el estado del usuario
        if(!$this->statusCompanyUser($user))
        {
            $queryError = [ 'querywall' => 'Error, El usuario debe pagar la suscripción' ];
            return $this->errorResponse( $queryError, 500 );
        }

        $quoteCompany = QuotesCompanies::where('quotes_id', $id)->where('company_id', $user->id);
        
        $name_company = $user->companyName();

        if($user->userType() != 'oferta'){
            $queryError = [ 'querywall' => 'Error, El no tiene privilegios para participar en una cotización' ];
            return $this->errorResponse( $queryError, 500 );
        }

        if($quoteCompany->exists()){
            $quoteCompaniesError = [ 'quotesCompanies' => 'La compañia ya se encuestra participando en esta cotización' ];
            return $this->errorResponse( $quoteCompaniesError, 500 );
        }

        DB::beginTransaction();

        $quote = Quotes::find($id);

        $quoteCompanyFields['quotes_id']       = $id;
        $quoteCompanyFields['company_id']      = $user->companyId();
        $quoteCompanyFields['user_id']         = $quote->user_id;
        $quoteCompanyFields['type']            = QuotesCompanies::TYPE_INTERESTED;
        $quoteCompanyFields['status']          = QuotesCompanies::STATUS_EARRING;
        $quoteCompanyFields['user_company_id'] = $user->id;

        try{
            $quoteCompany = QuotesCompanies::create( $quoteCompanyFields );
        }catch(\Throwable $th){
            DB::rollBack();
            $quoteCompanyError = [ 'question' => 'Error, no se ha podido gestionar la solicitud' ];
            return $this->errorResponse( $quoteCompanyError, 500 );
        }
        DB::commit();

        // $email = Quotes::find($id)->user->email;

        // Mail::to(trim($email))->send(new SendParticipateTenderCompany(
        //     Tenders::find($id)->name,
        //     $user->companyName()
        // ));

        // Enviar invitación por notificación
        // $notificationsIds   = [];
        // $notificationsIds[] = $quoteCompany->tender->user_id;
        // $notifications      = new Notifications();
        // $notifications->registerNotificationQuery( $quoteCompany, Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE, $notificationsIds );

        return $this->showOne($quoteCompany,201); 
    }

    public function statusCompanyUser($user)
    {
        if( $user->isAdminFrontEnd() ){
            $company = $user->company[0];
        }elseif( $user->team ){
            $company = $user->team->company;
        }

        return $company->companyStatusPayment();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
