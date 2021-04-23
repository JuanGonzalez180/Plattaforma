<?php

namespace App\Models;

use App\Models\Files;
use App\Models\Tenders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersVersions extends Model
{
    use HasFactory;

    const LICITACION_CREATED = 'Borrador';
    const LICITACION_PUBLISH = 'Publicada';
    const LICITACION_CLOSED = 'Cerrada';

    protected $fillable = [
        'tenders_id',
        'adenda',
        'price',
        'date',
        'hour',
        'status'
    ];

    public function isStatusCreated(){
        return $this->status == TendersVersions::LICITACION_CREATED;
    }

    public function isStatusPublish(){
        return $this->status == TendersVersions::LICITACION_PUBLISH;
    }

    public function isStatusClosed(){
        return $this->status == TendersVersions::LICITACION_CLOSED;
    }

    public function tenders(){
        return $this->belongsTo(Tenders::class);
    }

    public function files(){
        return $this->belongsToMany(Files::class);
    }
}
