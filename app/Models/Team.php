<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Transformers\TeamTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Paginatable;

class Team extends Model
{
    use HasFactory, Paginatable;

    protected $perPage = 9;

    public $transformer = TeamTransformer::class;

    protected $fillable = [
        'user_id',
        'company_id',
        'position',
        'phone',
        'status',
    ];

    const TEAM_PENDING = 'Pendiente';
    const TEAM_APPROVED = 'Aprobado';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
