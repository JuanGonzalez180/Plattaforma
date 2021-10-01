<?php

namespace App\Models;

use App\Models\User;
use App\Models\TendersCompanies;
use App\Transformers\NotificationsTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifications extends Model
{
    use HasFactory;
    public $transformer = NotificationsTransformer::class;
    
    const NOTIFICATION_TENDERSDECLINED = 'TendersDeclined';
    const NOTIFICATION_TENDERCOMPANYSELECTED = 'TenderCompanySelected';
    const NOTIFICATION_TENDERINVITECOMPANIES = 'TenderInviteCompanies';
    const NOTIFICATION_TENDERCOMPANYNOPARTICIPATE = 'TenderCompanyNoParticipate';
    const NOTIFICATION_TENDERRESPONSECOMPANIES = 'TenderResponseCompanies';
    const NOTIFICATION_TENDERCOMPANYPARTICIPATE = 'TenderCompanyParticipate';
    const NOTIFICATION_TENDERCOMPANYNEWVERSION = 'TenderCompanyNewVersion';

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

    public function queryId(){
        $this->query_id = $this->notificationsable_id;
        if( 
            $this->type == Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE && 
            $this->notificationsable_type == TendersCompanies::class
        ){
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id;
            }else{
                $this->query_id = '';
            }
        }elseif( 
            $this->type == Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE && 
            $this->notificationsable_type == TendersCompanies::class
        ){  
            $tenderCompanies = TendersCompanies::find($this->notificationsable_id);
            if( $tenderCompanies ){
                $this->query_id = $tenderCompanies->tender->project_id . '/' . $tenderCompanies->tender->id;
            }else{
                $this->query_id = '';
            }
        }

        return $this->query_id;
    }

    public $notifications = [
        Notifications::NOTIFICATION_TENDERSDECLINED => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'Se ha eliminado una licitación en la que estabas participando.' 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYSELECTED => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'Su propuesta ha sido seleccionada .' 
        ],
        Notifications::NOTIFICATION_TENDERINVITECOMPANIES => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'Su compañía ha sido invitada a una licitación.' 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'La compañía %s ha dejado de participar en la licitación.' 
        ],
        Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'Ha sido aprobada la solicitud', 
            'message2' => 'Ha sido rechazada la solicitud', 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'La compañía %s quiere participar en la licitación.',
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION => [ 
            'title' => 'Licitación: %s', 
            'subtitle' => '', 
            'message' => 'Se ha actualizado una licitación en la que estás participando',
        ],
    ];

    public function registerNotificationQuery( $query, $type, $usersIds, $params = [] ){
        // Notificación por Usuario
        $title = 'Notificación';
        $subtitle = '';
        $message = '';
        $data = [];

        $title = $this->notifications[$type]['title'];
        $subtitle = $this->notifications[$type]['subtitle'];
        $message = $this->notifications[$type]['message'];
        $data['type'] = $type;
        $data['id'] = $query->id;

        if( 
            $type == Notifications::NOTIFICATION_TENDERSDECLINED || 
            $type == Notifications::NOTIFICATION_TENDERINVITECOMPANIES ||
            $type == Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION 
        ){
            $title = sprintf($title, $query->name);
        }elseif( 
            $type == Notifications::NOTIFICATION_TENDERCOMPANYSELECTED
        ){
            $title = sprintf($title, $query->tender->name);
        }elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYNOPARTICIPATE ){
            $title = sprintf($title, $query->tender->name);
            $message = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id;
        }elseif( $type == Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES ){
            $title = sprintf($title, $query->tender->name);
            if( $query->status != 'Participando' ){
                $message = $this->notifications[$type]['message2'];
            }
        }elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE ){
            $title = sprintf($title, $query->tender->name);
            $message = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }

        $usersIds = array_unique($usersIds);
        foreach ($usersIds as $key => $user_id) {

            // query es Tender::class
            $query->notifications()->create([
                'title' => $title,
                'subtitle' => $subtitle,
                'message' => $message,
                'user_id' => $user_id, 
                'type' => $type
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
