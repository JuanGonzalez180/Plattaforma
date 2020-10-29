<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remarks extends Model
{
    use HasFactory;

    const REMARKS_CREATED = 'Creado';
    const REMARKS_HIDDEN = 'Oculto';
    const REMARKS_ANSWERED = 'Respondido';

    protected $fillable = [
        'user_id',
        'type',
        'type_id',
        'calification',
        'message',
        'status',
        'date',
        'date_update'
    ];

    public function isStatusCreated(){
        return $this->status == Remarks::REMARKS_CREATED;
    }

    public function isStatusHidden(){
        return $this->status == Remarks::REMARKS_HIDDEN;
    }

    public function isStatusAnswered(){
        return $this->status == Remarks::REMARKS_ANSWERED;
    }
}
