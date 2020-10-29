<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proponents extends Model
{
    use HasFactory;

    const PROPONENTS_REJECTED = 'Rechazado';
    const PROPONENTS_APPROVED = 'Aprobado';
    const PROPONENTS_PARTICIPATING = 'Participando';

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
}
