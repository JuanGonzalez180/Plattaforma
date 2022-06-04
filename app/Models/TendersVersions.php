<?php

namespace App\Models;

use App\Models\Tags;
use App\Models\Files;
use App\Models\Tenders;
use App\Models\Notifications;
use App\Transformers\TendersVersionsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendersVersions extends Model
{
    use HasFactory;

    public $transformer = TendersVersionsTransformer::class;

    const LICITACION_CREATED    = 'Borrador';
    const LICITACION_PUBLISH    = 'Publicada';
    const LICITACION_CLOSED     = 'Cerrada';
    const LICITACION_FINISHED   = 'Finalizada';
    const LICITACION_DECLINED   = 'Declinada';
    const LICITACION_DISABLED   = 'Inactiva';
    const LICITACION_DESERTED   = 'Desierta';

    const LICITACION_CLOSED_BLANK   = 'Indefinido';
    const LICITACION_CLOSED_USER    = 'Cerrado por el usuario';
    const LICITACION_CLOSED_SYSTEM  = 'Cerrado por el sistema';


    protected $fillable = [
        'tenders_id',
        'adenda',
        'price',
        'date',
        'hour',
        'status',
        'close'
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

    // Relacion uno a muchos polimorfica
    public function files(){
        return $this->morphMany(Files::class, 'filesable');
    }

    // Relacion uno a muchos polimorfica
    public function tags(){
        return $this->morphMany(Tags::class, 'tagsable');
    }

    public function tagsName()
    {
        return Tags::where('tagsable_id', $this->id)
            ->where('tagsable_type', TendersVersions::class)
            ->pluck('name')
            ->all();
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
}
