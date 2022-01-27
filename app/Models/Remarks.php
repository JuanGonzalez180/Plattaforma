<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Notifications;
use App\Transformers\RemarksTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remarks extends Model
{
    protected $guarded = [];

    use HasFactory;
    
    const REMARKS_CREATED = 'Creado';
    // const REMARKS_HIDDEN = 'Oculto';
    // const REMARKS_ANSWERED = 'Respondido';
    
    public $transformer = RemarksTransformer::class;

    protected $fillable = [
        'user_id',
        'company_id',
        'remarksable_id',
        'remarksable_type',
        'calification',
        'message',
        'status'
    ];

    protected $hidden = [
        'remarksable_id',
        'remarksable_type',
    ];

    public function isStatusCreated(){
        return $this->status == Remarks::REMARKS_CREATED;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function remarksable(){
        return $this->morphTo();
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
}
