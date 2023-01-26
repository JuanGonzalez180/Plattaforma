<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Team;
use App\Models\Company;
use App\Models\QueryWall;
use App\Models\TendersCompanies;
use App\Models\QuotesCompanies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Transformers\NotificationsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifications extends Model
{
    use HasFactory;
    public $transformer = NotificationsTransformer::class;
    

    // DESIGNAR COMO ADMINISTRADOR
    const NOTIFICATION_APPOINT_ADMINISTRATOR        = 'AppointAdministrator';

    // LICITACIONES
    const NOTIFICATION_TENDER_DELETE                = 'TendersDelete'; //notificación cuando se elimina una licitación
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
    
    // COTIZACIONES
    const NOTIFICATION_QUOTEINVITECOMPANIES         = 'QuoteInviteCompanies';
    const NOTIFICATION_QUOTECOMPANYNEWVERSION       = 'QuoteCompanyNewVersion';
    const NOTIFICATION_QUOTE_STATUS_CLOSED          = 'QuoteCompaniesStatusClosed'; //notificación cuando una cotización se cierra y se le debe enviar a las compañia licitantes
    const NOTIFICATION_QUOTE_STATUS_CLOSED_ADMIN    = 'QuoteCompaniesStatusClosedAdmin'; //notificación cuando una licitacion se cierra y se le debe enviar al encargado y administrador de la licitación
    const NOTIFICATION_QUOTE_CLOSED                 = 'QuoteStatusClosed';
    const NOTIFICATION_QUOTECOMPANY_OFFER           = 'QuoteCompanyOffer'; //Notificación cuando una compañia ha ofertado en una licitación
    const NOTIFICATION_QUOTE_INVITATION_APPROVED    = 'QuoteCompanyInvitationApproved'; //invitacion de cotización Aprobada
    const NOTIFICATION_QUOTE_INVITATION_REJECTED    = 'QuoteCompanyInvitationRejected'; //invitacion de cotización Rechazada
    const NOTIFICATION_QUOTE_STATUS_CLOSED_BEFORE   = 'QuoteCompaniesStatusClosedBefore'; //notificación cuando una cotización es cerrada antes de tiempo por el administrador y se le debe enviar a las compañia licitantes
    const NOTIFICATION_QUERYWALL_QUOTE_ADMIN        = 'QueryWallQuoteAdmin'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    const NOTIFICATION_QUERYWALL_QUOTE_QUESTION     = 'QueryWallQuoteQuestions'; //notificación cuando una empresa cotizante hace una pregunta en una cotización
    const NOTIFICATION_QUERYWALL_QUOTE_ANSWER       = 'QueryWallQuoteAnswer'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    const NOTIFICATION_RECOMMEND_QUOTE              = 'QuoteRecommend'; //Notifica recomendaciones a compañias con etiquetas en comun
    const NOTIFICATION_QUOTECOMPANYPARTICIPATE      = 'QuoteCompanyParticipate';
    const NOTIFICATION_QUOTERESPONSECOMPANIES       = 'QuoteResponseCompanies';
    
    //Muro de consultas
    const NOTIFICATION_QUERYWALL_TENDER_QUESTION    = 'QueryWallQuestions'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    const NOTIFICATION_QUERYWALL_TENDER_ANSWER      = 'QueryWallAnswer'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    const NOTIFICATION_QUERYWALL_TENDER_ADMIN       = 'QueryWallAdmin'; //notificación cuando una empresa licitante hace una pregunta en una licitación
    
    //Licitaciones
    const NOTIFICATION_TENDER_STATUS_CLOSED         = 'TenderCompaniesStatusClosed'; //notificación cuando una licitacion se cierra y se le debe enviar a las compañia licitantes
    const NOTIFICATION_TENDER_STATUS_CLOSED_BEFORE  = 'TenderCompaniesStatusClosedBefore'; //notificación cuando una licitacion es cerrada antes de tiempo por el administrador y se le debe enviar a las compañia licitantes
    const NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN   = 'TenderCompaniesStatusClosedAdmin'; //notificación cuando una licitacion se cierra y se le debe enviar al encargado y administrador de la licitación
    const NOTIFICATION_RECOMMEND_TENDER             = 'TenderRecommend'; //Notifica recomendaciones a compañias con etiquetas en comun

    protected $guarded = [];
    /**
     * type: Tipo de Archivo
     */
    protected $fillable = [
        'title',
        'subtitle',
        'message',
        'user_id',
        'type',
        'viewed'
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
        if($this->type == Notifications::NOTIFICATION_TENDERINVITECOMPANIES && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);
            if( $tender ){
                $this->query_id = $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }

        }
        if($this->type == Notifications::NOTIFICATION_QUOTEINVITECOMPANIES && $this->notificationsable_type == Quotes::class)
        {
            $quote = Quotes::find($this->notificationsable_id);
            if( $quote ){
                $this->query_id = $quote->company->slug."/cotizacion/".$quote->id;
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
        else if($this->type == Notifications::NOTIFICATION_QUOTE_INVITATION_REJECTED && $this->notificationsable_type == QuotesCompanies::class)
        {
            //cuando la compañia rechaza la invitación a una licitación.
            $quoteCompanies = QuotesCompanies::find($this->notificationsable_id);
            if( $quoteCompanies ){
                $this->query_id = $quoteCompanies->quote->project_id;
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
        else if($this->type == Notifications::NOTIFICATION_QUOTE_INVITATION_APPROVED && $this->notificationsable_type == QuotesCompanies::class)
        {
            //cuando la compañia rechaza la invitación a una cotización.
            $quoteCompanies = QuotesCompanies::find($this->notificationsable_id);
            if( $quoteCompanies ){
                $this->query_id = $quoteCompanies->quote->project_id . '/' . $quoteCompanies->quote->id;
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
        else if($this->type == Notifications::NOTIFICATION_QUOTECOMPANY_OFFER && $this->notificationsable_type == QuotesCompanies::class)
        {
            $quoteCompanies = QuotesCompanies::find($this->notificationsable_id);
            if( $quoteCompanies ){
                $this->query_id = $quoteCompanies->quote->project_id . '/' . $quoteCompanies->quote->id;
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
        elseif($this->type == Notifications::NOTIFICATION_QUOTECOMPANYPARTICIPATE && $this->notificationsable_type == QuotesCompanies::class)
        {  
            $quoteCompanies = QuotesCompanies::find($this->notificationsable_id);
            if( $quoteCompanies ){
                $this->query_id = $quoteCompanies->quote->project_id . '/' . $quoteCompanies->quote->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION && $this->notificationsable_type == QueryWall::class)
        {
            $tenderQuestion = QueryWall::find($this->notificationsable_id);

            if( $tenderQuestion ){
                $this->query_id = $tenderQuestion->queryWallTender()->company->slug."/licitacion/".$tenderQuestion->queryWallTenderId();
                // $this->query_id = $tenderQuestion->queryWallProjectId()  . '/' . $tenderQuestion->queryWallTenderId();
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_QUESTION && $this->notificationsable_type == QueryWall::class)
        {
            $quoteQuestion = QueryWall::find($this->notificationsable_id);

            if( $quoteQuestion ){
                // $this->query_id = $quoteQuestion->queryWallQuoteProjectId()  . '/' . $quoteQuestion->queryWallQuoteId();
                $this->query_id = $quoteQuestion->queryWallQuote()->company->slug."/cotizacion/".$quoteQuestion->queryWallQuoteId();
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
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_ANSWER && $this->notificationsable_type == QueryWall::class)
        {
            $quoteQuestion = QueryWall::find($this->notificationsable_id);

            if( $quoteQuestion ){
                $this->query_id = $quoteQuestion->queryWallQuote()->company->slug."/cotizacion/".$quoteQuestion->queryWallQuoteId();
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_TENDER_ADMIN && $this->notificationsable_type == QueryWall::class)
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
        else if($this->type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_ADMIN && $this->notificationsable_type == QueryWall::class)
        {
            $quoteQuestion = QueryWall::find($this->notificationsable_id);

            if( $quoteQuestion ){
                $this->query_id = $quoteQuestion->queryWallQuote()->company->slug."/cotizacion/".$quoteQuestion->queryWallQuoteId();
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED && $this->notificationsable_type == Quotes::class)
        {
            $quote = Quotes::find($this->notificationsable_id);

            if( $quote ){
                $this->query_id = $quote->company->slug."/cotizacion/".$quote->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_BEFORE && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_BEFORE && $this->notificationsable_type == Quotes::class)
        {
            $quotes = Quotes::find($this->notificationsable_id);

            if( $quotes ){
                $this->query_id = $quotes->company->slug."/cotizaciones/".$quotes->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->project_id . '/' . $tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_ADMIN && $this->notificationsable_type == Quotes::class)
        {
            $quote = Quotes::find($this->notificationsable_id);

            if( $quote ){
                $this->query_id = $quote->project_id . '/' . $quote->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_RECOMMEND_TENDER && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_RECOMMEND_QUOTE && $this->notificationsable_type == Quotes::class)
        {
            $quote = Quotes::find($this->notificationsable_id);

            if( $quote ){
                $this->query_id = $quote->company->slug."/cotizacion/".$quote->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION && $this->notificationsable_type == Tenders::class)
        {
            $tender = Tenders::find($this->notificationsable_id);

            if( $tender ){
                $this->query_id = $tender->company->slug."/licitacion/".$tender->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_QUOTECOMPANYNEWVERSION && $this->notificationsable_type == Quotes::class)
        {
            $quote = Quotes::find($this->notificationsable_id);

            if( $quote ){
                $this->query_id = $quote->company->slug."/cotizacion/".$quote->id;
            }else{
                $this->query_id = '';
            }
        }
        else if($this->type == Notifications::NOTIFICATION_TENDER_DELETE && $this->notificationsable_type == Tenders::class)
        {
            $this->query_id = '';
        }
        else if($this->type == Notifications::NOTIFICATION_APPOINT_ADMINISTRATOR && $this->notificationsable_type == Team::class)
        {
            $this->query_id = '';
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
            'message'   => 'La licitación ha sido adjudicada, la compañia %s ha sido selecciona como la mejor oferta, muchas gracias por participar.' 
        ],
        Notifications::NOTIFICATION_QUOTE_CLOSED => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'La cotización %s ha sido cerrada.' 
        ],
        Notifications::NOTIFICATION_TENDERINVITECOMPANIES => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Te han invitado a esta licitación.<br>Fecha de cierre: <b>%s</b>.' 
        ],
        Notifications::NOTIFICATION_QUOTEINVITECOMPANIES => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'Te han invitado a esta cotización.<br>Fecha de cierre: <b>%s</b>.' 
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
        Notifications::NOTIFICATION_QUOTE_INVITATION_REJECTED => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s ha rechazado la invitación.' 
        ],
        Notifications::NOTIFICATION_INVITATION_APPROVED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s, ha aprobado la invitación.' 
        ],
        Notifications::NOTIFICATION_QUOTE_INVITATION_APPROVED => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s, ha aprobado la invitación.' 
        ],
        Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Ha sido aprobada la solicitud', 
            // 'message2'  => 'Ha sido rechazada la solicitud', 
            'message2'  => 'La solicitud no fue aprobada', 
        ],
        Notifications::NOTIFICATION_QUOTERESPONSECOMPANIES => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'Ha sido aprobada la solicitud', 
            // 'message2'  => 'Ha sido rechazada la solicitud', 
            'message2'  => 'La solicitud no fue aprobada', 
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s quiere participar en la licitación.',
        ],
        Notifications::NOTIFICATION_QUOTECOMPANYPARTICIPATE => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañía %s quiere participar en la cotización.',
        ],
        Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Se ha creado una nueva adenda de la licitación.<br>Fecha de cierre: <b>%s</b>.',
        ],
        Notifications::NOTIFICATION_QUOTECOMPANYNEWVERSION => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'Se ha creado una nueva adenda de la cotización.<br>Fecha de cierre: <b>%s</b>.',
        ],
        Notifications::NOTIFICATION_TENDERCOMPANY_OFFER => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha ofertado en la licitación.',
        ],
        Notifications::NOTIFICATION_QUOTECOMPANY_OFFER => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha ofertado en la cotización.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION => [ 
            'title'     => 'Muro de consultas: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha hecho una pregunta.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_QUOTE_QUESTION => [ 
            'title'     => 'Muro de consultas: Cot. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha hecho una pregunta.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_TENDER_ANSWER => [ 
            'title'     => 'Muro de consultas: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha respondido tu pregunta.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_QUOTE_ANSWER => [ 
            'title'     => 'Muro de consultas: Cot. %s', 
            'subtitle'  => '', 
            'message'   => 'La compañia %s, ha respondido tu pregunta.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_TENDER_ADMIN => [ 
            'title'     => 'Muro de consultas: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'El encargado de la licitación ha hecho un anuncio.',
        ],
        Notifications::NOTIFICATION_QUERYWALL_QUOTE_ADMIN => [ 
            'title'     => 'Muro de consultas: Cot. %s', 
            'subtitle'  => '', 
            'message'   => 'El encargado de la cotización ha hecho un anuncio.',
        ],
        Notifications::NOTIFICATION_TENDER_STATUS_CLOSED => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'La licitación %s se ha cerrado y esta en proceso de evaluación.',
        ],
        Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            // 'message'   => 'La cotización %s entro en proceso de evaluación.',
            'message'   => 'La cotización %s se ha cerrado.',
        ],
        Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_BEFORE => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'El administrador ha cerrado la licitación %s antes de la fecha prevista y entrará en proceso de evaluación.',
        ],
        Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_BEFORE => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'El administrador ha cerrado la cotización %s antes de la fecha prevista y entrará en proceso de evaluación.',
        ],
        Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN => [ 
            'title'     => 'Licitación: Lic. %s', 
            'subtitle'  => '', 
            'message'   => 'La licitación %s se ha cerrado, procede a evaluar la licitación.',
        ],
        Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_ADMIN => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            // 'message'   => 'La cotización %s entro en proceso de evaluación, procede a evaluar la cotización.',
            'message'   => 'La cotización %s se ha cerrado, procede a revisar.',
        ],
        Notifications::NOTIFICATION_RECOMMEND_TENDER => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'Hay una licitación nueva que coincide con tus etiquetas.',
        ],
        Notifications::NOTIFICATION_RECOMMEND_QUOTE => [ 
            'title'     => 'Cotización: %s', 
            'subtitle'  => '', 
            'message'   => 'Hay una cotización nueva que coincide con tus etiquetas.',
        ],
        Notifications::NOTIFICATION_TENDER_DELETE => [ 
            'title'     => 'Licitación: %s', 
            'subtitle'  => '', 
            'message'   => 'El administrador de la licitación Licitación %s ha decidido cerrar y eliminar la licitación.',
        ],
        Notifications::NOTIFICATION_APPOINT_ADMINISTRATOR => [ 
            'title'     => 'Alerta: Cambio de usuario', 
            'subtitle'  => '', 
            'message'   => 'Te han asignado como administrador de la compañia',
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

        if( $type == Notifications::NOTIFICATION_TENDERSDECLINED )
        {
            $title = sprintf($title, $query->name);
        }
        elseif( 
            $type == Notifications::NOTIFICATION_TENDERCOMPANYSELECTED
        )
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
        }
        elseif( 
            $type == Notifications::NOTIFICATION_QUOTE_CLOSED
        )
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERINVITECOMPANIES )
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $this->formatDate($query->tendersVersionLast()->date));
            // $data['id'] = $query->company->slug."/licitacion/".$query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTEINVITECOMPANIES )
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $this->formatDate($query->quotesVersionLast()->date));
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
        elseif( $type == Notifications::NOTIFICATION_QUOTE_INVITATION_REJECTED )
        {
            $title      = sprintf($title, $query->quote->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->quote->project_id;
        }
        elseif( $type == Notifications::NOTIFICATION_INVITATION_APPROVED ) //notificacion cuando una compañia acepta una licitación
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTE_INVITATION_APPROVED ) //notificacion cuando una compañia acepta una cotización
        {
            $title      = sprintf($title, $query->quote->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->quote->project_id . '/' . $query->quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERRESPONSECOMPANIES ){
            $title = sprintf($title, $query->tender->name);
            if( $query->status != 'Participando' ){
                $message = $this->notifications[$type]['message2'];
            }
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTERESPONSECOMPANIES ){
            $title = sprintf($title, $query->quote->name);
            if( $query->status != 'Participando' ){
                $message = $this->notifications[$type]['message2'];
            }
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYPARTICIPATE ){
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTECOMPANYPARTICIPATE ){
            $title      = sprintf($title, $query->quote->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->quote->project_id . '/' . $query->quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANY_OFFER ) //notificación cuando una compañia oferta a una licitación
        {
            $title      = sprintf($title, $query->tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->tender->project_id . '/' . $query->tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTECOMPANY_OFFER ) //notificación cuando una compañia oferta a una cotización
        {
            $title      = sprintf($title, $query->quote->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $query->quote->project_id . '/' . $query->quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_TENDER_QUESTION ) //notificación cuando una compañia hace una pregunta a una licitación
        {
            $tender     = Tenders::find($query->querysable_id);

            $title      = sprintf($title, $tender->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $tender->company->slug."/licitacion/".$tender->id;
            // $data['id'] = $tender->project_id . '/' . $tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_QUESTION ) //notificación cuando una compañia hace una pregunta a una licitación
        {
            $quote      = Quotes::find($query->querysable_id);

            $title      = sprintf($title, $quote->name);
            $message    = sprintf($message, $query->company->name);
            $data['id'] = $quote->company->slug."/cotizacion/".$quote->id;
            // $data['id'] = $quote->project_id . '/' . $quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_TENDER_ANSWER ) //notificación cuando una compañia responde una pregunta a una licitación
        {
            $tender     = Tenders::find($query->querysable_id);

            $title      = sprintf($title, $tender->name);
            $message    = sprintf($message, $tender->company->name);
            $data['id'] = $tender->company->slug."/licitacion/".$tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_ANSWER ) //notificación cuando una compañia responde una pregunta a una cotización
        {
            $quote     = Quotes::find($query->querysable_id);

            $title      = sprintf($title, $quote->name);
            $message    = sprintf($message, $quote->company->name);
            $data['id'] = $quote->company->slug."/cotizacion/".$quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_TENDER_ADMIN ) //notificación cuando una compañia responde una pregunta a una licitación
        {
            $tender     = Tenders::find($query->querysable_id);

            $title      = sprintf($title, $tender->name);
            $message    = sprintf($message, $tender->company->name);
            $data['id'] = $tender->company->slug."/licitacion/".$tender->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUERYWALL_QUOTE_ADMIN ) //notificación cuando una compañia responde una pregunta a una cotización
        {
            $quote      = Quotes::find($query->querysable_id);

            $title      = sprintf($title, $quote->name);
            $message    = sprintf($message, $quote->company->name);
            $data['id'] = $quote->company->slug."/cotizacion/".$quote->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED ) //notificación cuando una licitación se cierra y le notifica a las empresas licitantes
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED ) //notificación cuando una cotización se cierra y le notifica a las empresas cotizantes
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_BEFORE ) //notificación cuando una licitación es cerrada por el administrador antes de tiempo y le notifica a las empresas licitantes
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_BEFORE ) //notificación cuando una cotización es cerrada por el administrador antes de tiempo y le notifica a las empresas licitantes
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDER_STATUS_CLOSED_ADMIN ) //notificación cuando una licitación se cierra y el encargado de la licitación o admin de la empresa procede a evaluar
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTE_STATUS_CLOSED_ADMIN ) //notificación cuando una cotización se cierra y el encargado de la cotización o admin de la empresa procede a evaluar
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_RECOMMEND_TENDER ) //notificación cuando una compañia tiene en comun sus etiqutas con alguna licitación
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_RECOMMEND_QUOTE ) //notificación cuando una compañia tiene en comun sus etiqutas con alguna cotización
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->name);
            $data['id'] = $query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDERCOMPANYNEWVERSION ) //notificación cuando se crea una adenda de la licitación
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $this->formatDate($query->tendersVersionLast()->date));
            $data['id'] = $query->company->slug."/licitacion/".$query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_QUOTECOMPANYNEWVERSION ) //notificación cuando se crea una adenda de la cotización
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $this->formatDate($query->quotesVersionLast()->date));
            $data['id'] = $query->company->slug."/cotizacion/".$query->id;
        }
        elseif( $type == Notifications::NOTIFICATION_TENDER_DELETE ) //notificación se borra una licitación
        {
            $title      = sprintf($title, $query->name);
            $message    = sprintf($message, $query->company->name);
        }
        elseif( $type == Notifications::NOTIFICATION_APPOINT_ADMINISTRATOR ) //notificación se borra una licitación
        {
            $title      = sprintf($title);
            $message    = sprintf($message);
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

    public function formatDate($date)
    {
        $date = Carbon::parse($date)->locale('es');
        return ucfirst($date->monthName).", ".$date->format('d-Y');
    }
}
