<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tenders;
use App\Models\Quotes;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\QueryWallTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueryWall extends Model
{
    use HasFactory;

    public $transformer = QueryWallTransformer::class;

    const QUERYWALL_ANSWERED    = 'Respondido';
    const QUERYWALL_PUBLISH     = 'Publicado';

    const QUERYWALL_VISIBLE     = 'Visible';
    const QUERYWALL_VISIBLE_NO  = 'No-Visible';

    const TYPE_QUERY            = 'Consulta';
    const TYPE_GLOBALMESSAGE    = 'Mensaje-Global';

    protected $fillable = [
        'querysable_id',
        'querysable_type',
        'company_id',
        'question',
        'date_questions',
        'answer',
        'date_answer',
        'user_id',
        'status',
        'user_answer_id',
        'visible',
        'type'
    ];

    protected $hidden = [
        'querysable_id',
        'querysable_type',
    ];

    public function querysable(){
        return $this->morphTo();
    }

    public function isPublish(){
        return $this->status == QueryWall::QUERYWALL_PUBLISH;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function user_answer(){
        return $this->belongsTo(User::class, 'user_answer_id', 'id');
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function queryWallTenderUser() {
        return Tenders::find($this->querysable_id)->user_id;
    }

    public function queryWallQuoteUser() {
        return Quotes::find($this->querysable_id)->user_id;
    }

    public function queryWallTender() {
        return Tenders::find($this->querysable_id);
    }

    public function queryWallTenderId() {
        return Tenders::find($this->querysable_id)->id;
    }

    public function queryWallProjectUser() {
        return Tenders::find($this->querysable_id)->project->user_id;
    }

    public function queryWallQuoteProjectUser(){
        return Quotes::find($this->querysable_id)->project->user_id;
    }

    public function queryWallProjectId() {
        return Tenders::find($this->querysable_id)->project->id;
    }

    public function notifications(){
        return $this->morphMany(Notifications::class, 'notificationsable');
    }
}
