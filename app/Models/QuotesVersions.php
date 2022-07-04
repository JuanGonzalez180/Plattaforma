<?php

namespace App\Models;

use App\Models\Tags;
use App\Models\Files;
use App\Models\Quotes;
use App\Models\Notifications;
use App\Transformers\QuotesVersionsTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotesVersions extends Model
{
    use HasFactory;

    public $transformer = QuotesVersionsTransformer::class;

    const QUOTATION_CREATED    = 'Borrador';
    const QUOTATION_PUBLISH    = 'Abierta';
    const QUOTATION_CLOSED     = 'En evaluación';
    const QUOTATION_FINISHED   = 'Cerrada';


    const QUOTATION_CLOSED_BLANK   = 'Indefinido';
    const QUOTATION_CLOSED_USER    = 'Cerrado por el usuario';
    const QUOTATION_CLOSED_SYSTEM  = 'Cerrado por el sistema';

    protected $fillable = [
        'quotes_id',
        'adenda',
        'price',
        'date',
        'hour',
        'status',
        'close'
    ];

    public function isStatusCreated(){
        return $this->status == QuotesVersions::QUOTATION_CREATED;
    }

    public function isStatusPublish(){
        return $this->status == QuotesVersions::QUOTATION_PUBLISH;
    }

    public function isStatusClosed(){
        return $this->status == QuotesVersions::QUOTATION_CLOSED;
    }

    public function quotes(){
        return $this->belongsTo(Quotes::class);
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
            ->where('tagsable_type', QuotesVersions::class)
            ->pluck('name')
            ->all();
    }

    // Relacion uno a muchos polimorfica
    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
}
