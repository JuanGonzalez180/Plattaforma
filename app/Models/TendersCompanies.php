<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Remarks;
use App\Models\TendersVersions;
use App\Models\Notifications;
use App\Transformers\TendersCompaniesTransformer;
use App\Transformers\TendersMyCompanyTransformer;
use App\Transformers\TendersCompanySelectedTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersCompanies extends Model
{
    use HasFactory;

    public $transformer                         = TendersCompaniesTransformer::class;

    //CUSTOM_TRANSFORMER
    const TRANSFORMER_TENDER_MY_COMPANY         = TendersMyCompanyTransformer::class;
    const TRANSFORMER_TENDER_COMPANY_SELECTED   = TendersCompanySelectedTransformer::class;

    const TYPE_INTERESTED       = 'Interesado';
    const TYPE_INVITED          = 'Invitado';
    
    const STATUS_EARRING_INVITATION  = 'Solicitud Pendiente'; //cuando el admin de la licitación acepta la solicitud

    const STATUS_EARRING        = 'Pendiente';
    const STATUS_PARTICIPATING  = 'Participando';
    const STATUS_REJECTED       = 'Rechazado';
    const STATUS_PROCESS        = 'Proceso';
    
    const WINNER_TRUE           = 'true';
    const WINNER_FALSE          = 'false';

    const PERCENTAGE_COMMISSION = 'porcentaje';
    const VALUE_COMMISSION      = 'valor';


    protected $fillable = [
        'tender_id',
        'company_id',
        'user_company_id',
        'user_id',
        'type',
        'price',
        'status',
        'winner',
        'commission'
    ];

    public function tender(){
        return $this->belongsTo(Tenders::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function userCompany()
    {
        return $this->belongsTo(User::class, 'user_company_id', 'id');
    }

    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    public function priceTransformer(){
        $price = 0;
        $statusPrice = 'false';
        $tender = Tenders::where('id', $this->tender_id)->first();
        $version = $tender->tendersVersionLastPublish();

        if( $version->status==TendersVersions::LICITACION_CLOSED || $version->status==TendersVersions::LICITACION_FINISHED ){
            $price = $this->price;
        }
        
        if( $this->price > 0 ){
            $statusPrice = 'true';
        }

        return [
            "price" => $price,
            "status" => $statusPrice,
        ];
    }

    // Relacion uno a muchos polimorfica
    public function remarks(){
        return $this->morphMany(Remarks::class, 'remarksable');
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }

    public function tenderCompanyEmails()
    {
        return array_unique([$this->company->user->email, $this->userCompany->email]);
    }

    public function tenderCompanyUsersIds()
    {
        return array_unique([$this->company->user->id, $this->userCompany->id]);
    }
}
