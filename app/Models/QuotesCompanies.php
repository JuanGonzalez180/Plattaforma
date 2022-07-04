<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\QuotesVersions;
use App\Models\Quotes;
use App\Models\Notifications;
use App\Transformers\QuotesCompaniesTransformer;
use App\Transformers\QuotesMyCompanyTransformer;
use App\Transformers\QuotesCompanySelectedTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotesCompanies extends Model
{
    use HasFactory;

    public $transformer                         = QuotesCompaniesTransformer::class;

    //CUSTOM_TRANSFORMER
    const TRANSFORMER_QUOTE_MY_COMPANY         = QuotesMyCompanyTransformer::class;
    const TRANSFORMER_QUOTE_COMPANY_SELECTED   = QuotesCompanySelectedTransformer::class;

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
        'quotes_id',
        'company_id',
        'user_company_id',
        'user_id',
        'type',
        'price',
        'status',
        'winner',
        'commission'
    ];

    public function quote(){
        return $this->belongsTo(Quotes::class);
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
        $price          = 0;
        $statusPrice    = 'false';
        $quote          = Quotes::where('id', $this->quotes_id)->first();
        $version        = $quote->quotesVersionLastPublish();

        if( $version->status==QuotesVersions::QUOTATION_CLOSED || $version->status==QuotesVersions::QUOTATION_FINISHED ){
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

    public function quoteCompanyEmails()
    {
        return array_unique([$this->company->user->email, $this->userCompany->email]);
    }

    public function quoteCompanyUsersIds()
    {
        return array_unique([$this->company->user->id, $this->userCompany->id]);
    }
}
