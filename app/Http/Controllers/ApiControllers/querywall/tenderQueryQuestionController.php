<?php

namespace App\Http\Controllers\ApiControllers\querywall;

use JWTAuth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tenders;
use App\Models\QueryWall;
use Illuminate\Http\Request;
use App\Models\Notifications;
use Illuminate\Validation\Rule;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\QueryWall\sendGlobalMessage;
use App\Mail\QueryWall\sendQuestionMessage;
use App\Http\Controllers\ApiControllers\ApiController;

class tenderQueryQuestionController extends ApiController
{
    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index(Request $request)
    {
        $tender_id = $request->tender_id;

        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        // if($user->userType() != 'oferta'){
        //     $queryError = [ 'querywall' => 'Error, El usuario no puede ver las preguntas' ];
        //     return $this->errorResponse( $queryError, 500 );
        // }

        $queryWalls  = QueryWall::where('querysable_id', $tender_id)
            ->where('querysable_type', Tenders::class)
            ->where('visible', QueryWall::QUERYWALL_VISIBLE)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->showAllTransformer($queryWalls);
        // return $this->showAll($queryWalls);
    }

    public function store(Request $request)
    {

        $user = $this->validateUser();
        $company_id = $user->companyId();

        $rules = [
            'question' => 'required|max:1000'
        ];

        $this->validate($request, $rules);

        DB::beginTransaction();

        $questionFields = $request->all();
        $questionFields['querysable_id']        = $request->tender_id;
        $questionFields['querysable_type']      = Tenders::class;
        $questionFields['company_id']           = $company_id;
        $questionFields['user_id']              = $user->id;
        $questionFields['question']             = $request->question;
        $questionFields['date_questions']       = Carbon::now();
        $questionFields['status']               = QueryWall::QUERYWALL_PUBLISH;

        //*si el mismo administrador de la licitación envia una pregunta se hace una pregunta es un mensaje global, pero si es un participante de la licitacion es una pregunta
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
            $this->sendNotificationQueryProponents($question, Notifications::NOTIFICATION_QUERYWALL_TENDER_ADMIN);

            // *Correos de las compañias participantes de la licitación.
            $tenderCompaniesEmails = $question->queryWallTender()->TenderParticipatingCompanyEmails();

            foreach ($tenderCompaniesEmails as $email) {
                Mail::to(trim($email))->send(new sendGlobalMessage(
                    $question->queryWallTender()->company->name,
                    $question->queryWallTender()->name,
                    $question->queryWallTender()->id,
                    $question->queryWallTender()->company->slug,
                    $question->question
                ));
            }

        } else {
            $this->sendNotificationQueryAdmin($question, Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION);
            // *Correos del administrador y encargado de la licitación.
            $terderAdminEmail = $question->queryWallTender()->TenderAdminEmails();

            foreach ($terderAdminEmail as $email) {
                Mail::to(trim($email))->send(new sendQuestionMessage(
                    $question->user->companyFull()->name,
                    $question->queryWallTender()->name,
                    $question->queryWallTender()->id,
                    $question->queryWallTender()->company->slug,
                    $question->question
                ));
            }
        }

        return $this->showOne($question, 201);
    }

    public function sendNotificationQueryProponents($query, $typeNotification)
    {
        $tenderCompanies = TendersCompanies::where('tender_id', $query->querysable_id)
            ->where('status', TendersCompanies::STATUS_PARTICIPATING)
            ->get();

        $users = [];
        foreach ($tenderCompanies as $value) {
            $users[] = $value->company->user->id;
            $users[] = $value->user_company_id;
        }

        $notifications  = new Notifications();
        $notifications->registerNotificationQuery($query, $typeNotification, $users);
    }

    public function sendNotificationQueryAdmin($query, $typeNotification)
    {
        $tender = Tenders::find($query->querysable_id);

        $notificationsIds   = [];
        $notificationsIds[] = $tender->user_id; // responsable de la licitación
        $notificationsIds[] = $tender->company->user_id; //administrador de la compañia

        $notificationsIds   = array_values(array_unique($notificationsIds));

        $notifications  = new Notifications();
        $notifications->registerNotificationQuery($query, $typeNotification, $notificationsIds);
    }

    public function update(Request $request, $id)
    {
        $user = $this->validateUser();

        if ($user->userType() != 'oferta') {
            $queryError = ['querywall' => 'Error, El usuario no puede modificar preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Tenders::class)
            ->first();

        if (!$queryAnswer) {
            $queryError = ['querywall' => 'Error, La pregunta no exite en el muro de consultas de licitaciones'];
            return $this->errorResponse($queryError, 500);
        }

        if (($queryAnswer->user_id == $user->id) || $user->isAdminFrontEnd()){

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

    public function destroy(int $id)
    {
        $user = $this->validateUser();

        if ($user->userType() != 'oferta') {
            $queryError = ['querywall' => 'Error, El usuario no puede borrar preguntas'];
            return $this->errorResponse($queryError, 500);
        }

        $queryAnswer = QueryWall::where('id', $id)
            ->where('querysable_type', Tenders::class)
            ->first();

        if (!$queryAnswer) {
            $queryError = ['querywall' => 'Error, La pregunta no exite en el muro de consultas de licitaciones'];
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
