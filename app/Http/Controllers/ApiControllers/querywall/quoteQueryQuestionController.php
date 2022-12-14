<?php

namespace App\Http\Controllers\ApiControllers\querywall;

use JWTAuth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Quotes;
use App\Models\QueryWall;
use Illuminate\Http\Request;
use App\Models\Notifications;
use Illuminate\Validation\Rule;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\QueryWall\sendQuestionQuoteMessage;
use App\Mail\QueryWall\sendGlobalQuoteMessage;
use App\Http\Controllers\ApiControllers\ApiController;

class quoteQueryQuestionController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $quote_id = $request->quote_id;

        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $queryWalls  = QueryWall::where('querysable_id', $quote_id)
            ->where('querysable_type', Quotes::class)
            ->where('visible', QueryWall::QUERYWALL_VISIBLE)
            ->orderBy('updated_at', 'asc')
            ->get();

        return $this->showAllTransformer($queryWalls);
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
    public function store(Request $request)
    {
        $user       = $this->validateUser();
        $company_id = $user->companyId();

        $rules = [
            'question' => 'required|max:1000'
        ];

        $this->validate($request, $rules);

        DB::beginTransaction();

        $questionFields = $request->all();
        $questionFields['querysable_id']        = $request->quote_id;
        $questionFields['querysable_type']      = Quotes::class;
        $questionFields['company_id']           = $company_id;
        $questionFields['user_id']              = $user->id;
        $questionFields['question']             = $request->question;
        $questionFields['date_questions']       = Carbon::now();
        $questionFields['status']               = QueryWall::QUERYWALL_PUBLISH;
        //*si el mismo administrador de la cotización envia una pregunta se hace una pregunta es un mensaje global, pero si es un participante de la cotización es una pregunta
        $questionFields['type']                 = ($user->userType() != 'oferta') ? QueryWall::TYPE_GLOBALMESSAGE : QueryWall::TYPE_QUERY;

        try {
            $question = QueryWall::create($questionFields);
        } catch (\Throwable $th) {
            $errorquestion = true;
            DB::rollBack();
            $questionError = ['question' => 'Error, no se ha podido crear la pregunta'];
            return $this->errorResponse($questionError, 500);
        }
        DB::commit();

        if ($user->userType() != 'oferta') {
            // NOTIFICACIONES:
            $this->sendNotificationQueryProponents($question, Notifications::NOTIFICATION_QUERYWALL_QUOTE_ADMIN);
            // CORREOS:
            $QuoteCompaniesEmails = $question->queryWallQuote()->QuoteParticipatingCompanyEmails();

            foreach ($QuoteCompaniesEmails as $email) {
                Mail::to(trim($email))->send(new sendGlobalQuoteMessage(
                    $question->queryWallQuote()->company->name,
                    $question->queryWallQuote()->name,
                    $question->queryWallQuote()->id,
                    $question->queryWallQuote()->company->slug,
                    $question->question
                ));
            }
        } else {
            // NOTIFICACIONES:
            $this->sendNotificationQueryAdmin($question, Notifications::NOTIFICATION_QUERYWALL_QUOTE_QUESTION);
            // CORREOS:
            $quoteAdminEmail = $question->queryWallQuote()->QuoteAdminEmails();

            foreach ($quoteAdminEmail as $email) {
                Mail::to(trim($email))->send(new sendQuestionQuoteMessage(
                    $question->user->companyFull()->name,
                    $question->queryWallQuote()->name,
                    $question->queryWallQuote()->id,
                    $question->queryWallQuote()->company->slug,
                    $question->question
                ));
            }

        }

        return $this->showOne($question, 201);
    }

    public function sendNotificationQueryAdmin($query, $typeNotification)
    {
        $quote = Quotes::find($query->querysable_id);

        $notificationsIds   = [];
        $notificationsIds[] = $quote->user_id; // responsable de la cotización
        $notificationsIds[] = $quote->company->user_id; //administrador de la compañia

        $notificationsIds   = array_values(array_unique($notificationsIds));

        $notifications  = new Notifications();
        $notifications->registerNotificationQuery($query, $typeNotification, $notificationsIds);
    }

    public function sendNotificationQueryProponents($query, $typeNotification)
    {
        $quotesCompanies = QuotesCompanies::where('quotes_id', $query->querysable_id)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();

        $users = [];
        foreach ($quotesCompanies as $value) {
            $users[] = $value->company->user->id;
            $users[] = $value->user_company_id;
        }

        $notifications  = new Notifications();
        $notifications->registerNotificationQuery($query, $typeNotification, $users);
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
        $user = $this->validateUser();

        if ($user->userType() != 'oferta') {
            $queryError = ['querywall' => 'Error, El usuario no puede modificar preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Quotes::class)
            ->first();

        if (!$queryAnswer) {
            $queryError = ['querywall' => 'Error, La pregunta no exite en el muro de consultas de cotizaciones.'];
            return $this->errorResponse($queryError, 500);
        }

        if (($queryAnswer->user_id == $user->id) || $user->isAdminFrontEnd()) {

            DB::beginTransaction();
            $queryFields['question'] = $request['question'];

            try {
                $queryAnswer->update($queryFields);
            } catch (\Throwable $th) {
                DB::rollBack();
                $questionError = ['question' => 'Error, no se ha podido modificar la pregunta'];
                return $this->errorResponse($questionError, 500);
            }
            DB::commit();

            return $this->showOne($queryAnswer, 200);
        } else {
            $queryError = ['querywall' => 'Error, El usuario no tiene privilegios para modificar la pregunta del muro de consultas'];
            return $this->errorResponse($queryError, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->validateUser();

        if ($user->userType() != 'oferta') {
            $queryError = ['querywall' => 'Error, El usuario no puede borrar preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Quotes::class)
            ->first();

        if (!$queryAnswer) {
            $queryError = ['querywall' => 'Error, La pregunta no exite en el muro de consultas de cotizaciones.'];
            return $this->errorResponse($queryError, 500);
        }

        if (($queryAnswer->user_id == $user->id) || $user->isAdminFrontEnd()) {
            $queryAnswer->delete();
        } else {
            $queryError = ['querywall' => 'Error, El usuario no tiene privilegios para barrar la pregunta del muro de consultas'];
            return $this->errorResponse($queryError, 500);
        }

        return $this->showOneData(['success' => 'Se ha eliminado correctamente la pregunta del muro de consultas', 'code' => 200], 200);
    }
}
