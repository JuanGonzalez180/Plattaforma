<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersCompanies extends Model
{
    use HasFactory;

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
        'type',
        'price',
        'status',
        'winner'
    ];
}
