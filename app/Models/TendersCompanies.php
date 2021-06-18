<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Transformers\TendersCompaniesTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersCompanies extends Model
{
    use HasFactory;

    public $transformer = TendersCompaniesTransformer::class;

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

}
