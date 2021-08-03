<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Remarks;
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
    
    const STATUS_EARRING        = 'Pendiente';
    const STATUS_PARTICIPATING  = 'Participando';
    const STATUS_REJECTED       = 'Rechazado';
    const STATUS_PROCESS        = 'Proceso';
    
    const WINNER_TRUE           = 'true';
    const WINNER_FALSE          = 'false';


    protected $fillable = [
        'tender_id',
        'company_id',
        'user_id',
        'type',
        'price',
        'status',
        'winner'
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

    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    // Relacion uno a muchos polimorfica
    public function remarks(){
        return $this->morphMany(Remarks::class, 'remarksable');
    }

}
