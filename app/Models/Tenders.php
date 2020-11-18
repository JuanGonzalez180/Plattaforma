<?php

namespace App\Models;

use App\Company;
use App\Interests;
use App\Projects;
use App\User;
use App\Remarks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenders extends Model
{
    use HasFactory;

    const LICITACION_CREATED = 'Borrador';
    const LICITACION_PUBLISH = 'Publicada';
    const LICITACION_CLOSED = 'Cerrada';

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'company_id',
        'user_id',
        'status',
        'date',
        'date_update'
    ];

    public function isStatusCreated(){
        return $this->status == Tenders::LICITACION_CREATED;
    }

    public function isStatusPublish(){
        return $this->status == Tenders::LICITACION_PUBLISH;
    }

    public function isStatusClosed(){
        return $this->status == Tenders::LICITACION_CLOSED;
    }

    public function project(){
        return $this->belongsTo(Projects::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    
    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function interests(){
        return $this->belongsToMany(Interests::class);
    }

    public function queryWalls(){
        return $this->hasMany(QueryWall::class);
    }

    public function remarks(){
        return $this->hasMany(Remarks::class);
    }
}
