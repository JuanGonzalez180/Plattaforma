<?php

namespace App\Models;

use App\Models\User;
use App\Models\QueryWall;
use App\Models\TendersCompanies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Transformers\NotificationsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifications extends Model
{
    use HasFactory;
    public $transformer = NotificationsTransformer::class;
    
    const NOTIFICATION_TENDERSDECLINED              = 'TendersDeclined';
    const NOTIFICATION_TENDERCOMPANYSELECTED        = 'TenderCompanySelected';
    const NOTIFICATION_TENDERINVITECOMPANIES        = 'TenderInviteCompanies';
    const NOTIFICATION_TENDERCOMPANYNOPARTICIPATE   = 'TenderCompanyNoParticipate';
    const NOTIFICATION_INVITATION_REJECTED          = 'TenderCompanyInvitationRejected'; //invitacion de licitacion Rechazada
    const NOTIFICATION_INVITATION_APPROVED          = 'TenderCompanyInvitationApproved'; //invitacion de licitacion Aprobada
    const NOTIFICATION_TENDERRESPONSECOMPANIES      = 'TenderResponseCompanies';
    const NOTIFICATION_TENDERCOMPANYPARTICIPATE     = 'TenderCompanyParticipate';
    const NOTIFICATION_TENDERCOMPANYNEWVERSION      = 'TenderCompanyNewVersion';
    const NOTIFICATION_TENDERCOMPANY_OFFER          = 'TenderCompanyOffer'; //Notificación cuando una compañia ha ofertado en una licitación

    //Muro de consultas
    const NOTIFICATION_QUERYWALL_TENDER_QUESTION    = 'QueryWallQuestions'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    const NOTIFICATION_QUERYWALL_TENDER_ANSWER      = 'QueryWallAnswer'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    
    //Licitaciones
    const NOTIFICATION_TENDER_STATUS_CLOSED         = 'TenderCompaniesStatusClosed'; //notificación cuando una licitacion se cierra y se le debe enviar a las compañia licitantes
    
    protected $guarded = [];
    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'title',
        'subtitle',
        'message',
        'user_id',
        'type'
    ];

    protected $hidden = [
        'notificationsable_id',
        'notificationsable_type',
    ];
    
    public function notificationsable(){
        return $this->morphTo();
    }

    public function queryId()
    {
        $this->query_id = $this->notificationsable_id;

        if($this->type == Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE && $this->notificationsable_type == TendersCompanies::class)
        {
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id;
            }else{
                $this->query_id = '';
            }

        }
        else if($this->type == Notifications::NOTIFICATION_INVITATION_REJECTED && $this->notificationsable_type == TendersCompanies::class)
        {
            //cuando la compañia rechaza la invitación a una licitación.
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id;
            }else{
                $this->query_id = '';
            }

        }
        else if($this->type == Notifications::NOTIFICATION_INVITATION_APPROVED && $this->notificationsable_type == TendersCompanies::class)
        {
            //cuando la compañia rechaza la invitación a una licitación.
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id . '/' . $tenderCompanies->tender->id;
            }else{
                $this->query_id = '';
            }

        }
        else if($this->type == Notifications::NOTIFICATION_TENDERCOMPANY_OFFER && $this->notificationsable_type == TendersCompanies::class)
        {
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id . '/' . $tenderCompanies->tender->id;
            }else{
                $this->query_id = '';
            }

        }
        elseif($this->type == Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE && $this->notificationsable_type == TendersCompanies::class)
        {  
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id . '/' . $tenderCompanies->tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION && $this->notificationsable_type == QueryWall::class)
        {
            $tenderQuestion = QueryWall::find($this->notificationsable_id);

            if( $tenderQuestion ){
                $this->query_id = $tenderQuestion->queryWallProjectId()  . '/' . $tenderQuestion->queryWallTenderId();
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_TENDER_ANSWER && $this->notificationsable_type == QueryWall::class)
        {
            $tenderQuestion = QueryWall::find($this->notificationsable_id);

            if( $tenderQuestion ){
                // $this->query_id = $tenderQuestion->queryWallTenderId();
                $this->query_id = $tenderQuestion->queryWallTender()->company->slug."/licitacion/".$tenderQuestion->queryWallTenderId();
                // $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->id;
            }else{
                $this->query_id = '';
            }
        }

        return $this->query_id;
    }

    public $notifications = [
        Notifications::NOTIFICATION_TENDERSDECLINED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Se ha eliminado una licitación en la que estabas participando.' 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYSELECTED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Su propuesta ha sido seleccionada .' 
        ],
        Notifications::NOTIFICATION_TENDERINVITECOMPANIES => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía ha sido invitada a la licitación: %s.' 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s se ha retirado de la licitación.' 
        ],
        Notifications::NOTIFICATION_INVITATION_REJECTED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s ha rechazado la invitación.' 
        ],
        Notifications::NOTIFICATION_INVITATION_APPROVED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s, ha aprobado la invitación.' 
        ],
        Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Ha sido aprobada la solicitud', 
            'message2'  => 'Ha sido rechazada la solicitud', 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s quiere participar en la licitación.',
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Se ha actualizado una licitación en la que estás participando',
        ],
        Notifications::NOTIFICATION_TENDERCOMPANY_OFFER => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha ofertado en la licitación.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION => [ 
            'title'     => 'Muro de consultas: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha hecho una pregunta.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_TENDER_ANSWER => [ 
            'title'     => 'Muro de consultas: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha respondido tu pregunta.',
        ],
        Notifications::NOTIFICATION_TENDER_STATUS_CLOSED => [ 
            'title'     => 'Licitación: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La licitación %s se ha cerrado.',
        ],
    ];

    public function registerNotificationQuery( $query, $type, $usersIds, $params = [] ){
        // Notificación por Usuario
        $title      = 'Notificación';
        $subtitle   = '';
        $message    = '';
        $data       = [];

        $title          = $this->notifications[$type]['title'];
        $subtitle       = $this->notifications[$type]['subtitle'];
        $message        = $this->notifications[$type]['message'];
        $data['type']   = $type;
        $data['id']     = $query->id;

        if( 
            $type == Notifications::NOTIFICATION_TENDERSDECLINED || 
            $type == Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION
        ){
            $title = sprintf($title, $query->name);
        }elseif( 
            $type == Notifications::NOTIFICATION_TENDERCOMPANYSELECTED
        ){
            $title      = sprintf($title, $query->tender->name);
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERINVITECOMPANIES )
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE )
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id;
        }
        elseif( $type == Notifications::NOTIFICATION_INVITATION_REJECTED )
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id;
        }
        elseif( $type == Notifications::NOTIFICATION_INVITATION_APPROVED ) //notificacion cuando una compañia acepta una licitación
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES ){
            $title = sprintf($title, $query->tender->name);
            if( $query->status != 'Participando' ){
                $message = $this->notifications[$type]['message2'];
            }
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE ){
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANY_OFFER ) //notificación cuando una compañia oferta a una licitación
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION ) //notificación cuando una compañia hace una pregunta a una licitación
        {
            $tender     = Tenders::find($query->querysable_id);

            $title      = sprintf($title, $tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $tender->project_id . '/' . $tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_TENDER_ANSWER ) //notificación cuando una compañia responde una pregunta a una licitación
        {
            $tender     = Tenders::find($query->querysable_id);

            $title      = sprintf($title, $tender->name);
            $message    = sprintf($message, $tender->company->name);
            $data['id'] = $tender->company->slug."/licitacion/".$tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED ) //notificación cuando una licitación se cierra y le notifica a las empresas licitantes
        {
            // $tender     = Tenders::find($query->querysable_id);
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }


        $usersIds = array_unique($usersIds);

        foreach ($usersIds as $key => $user_id) {

            // query es Tender::class
            $query->notifications()->create([
                'title'     => $title,
                'subtitle'  => $subtitle,
                'message'   => $message,
                'user_id'   => $user_id, 
                'type'      => $type
            ]);
        }

        $this->sendNotifications( $usersIds,  $title, $subtitle, $message, $data );
    }

    public function sendNotifications( $usersIds,  $title='', $subtitle='', $message='', $dataMessage = [] ){
        $FcmToken = [];
        foreach ($usersIds as $key => $user_id) {
            $user = User::find($user_id);
            foreach ($user->tokens as $key => $token) {
                array_push( $FcmToken, $token->token );
            }
        }
        $FcmToken = array_unique($FcmToken);

        if( count($FcmToken) ){
            $url = 'https://fcm.googleapis.com/fcm/send';
              
            $serverKey = env('FIREBASE_SECRET');
            
            $data = [
                "registration_ids" => $FcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message
                ]
            ];

            if( count($dataMessage) ){
                $data['data'] = $dataMessage;
            }

            $encodedData = json_encode($data);

            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];
        
            $ch = curl_init();
          
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
    
            // Execute post
            $result = curl_exec($ch);
    
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }        
    
            // Close connection
            curl_close($ch);
        }
    }
}
