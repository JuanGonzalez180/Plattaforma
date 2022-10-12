<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Traits\Paginatable;
use App\Models\Notifications;
use App\Transformers\TeamTransformer;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\TeamDetailTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory, Paginatable;

    protected $perPage = 9;


    public $transformer = TeamTransformer::class;
    const TRANSFORMER_TEAM_COMPANY = TeamDetailTransformer::class;

    protected $fillable = [
        'user_id',
        'company_id',
        'position',
        'phone',
        'status'
    ];

    const TEAM_PENDING  = 'Pendiente';
    const TEAM_APPROVED = 'Aprobado';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
}
