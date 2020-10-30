<?php

namespace App\Models;

use App\Company;
use App\User;
use App\Tenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proponents extends Model
{
    use HasFactory;

    const PROPONENTS_REJECTED = 'Rechazado';
    const PROPONENTS_APPROVED = 'Aprobado';
    const PROPONENTS_PARTICIPATING = 'Participando';

    const TYPE_INVITED = 'Invitado';
    const TYPE_INTERESTED = 'Interesado';

    const PROPONENTS_WINNER = 'True';
    const PROPONENTS_NO_WINNER = 'False';

    /**
     * Type: Tipo Invitado, Interesado
     */
    protected $fillable = [
        'licitacion_id',
        'type',
        'date_aceptacion',
        'user_id',
        'company_id',
        'winner',
        'status',
        'date',
        'date_update'
    ];

    public function isParticipating(){
        return $this->status == Proponents::PROPONENTS_PARTICIPATING;
    }

    public function isApproved(){
        return $this->status == Proponents::PROPONENTS_APPROVED;
    }

    public function isRejected(){
        return $this->status == Proponents::PROPONENTS_REJECTED;
    }

    public function isInvited(){
        return $this->type == Proponents::TYPE_INVITED;
    }

    public function isWinner(){
        return $this->winner == Proponents::PROPONENTS_WINNER;
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tenders(){
        return $this->belongsTo(Tenders::class);
    }
}
