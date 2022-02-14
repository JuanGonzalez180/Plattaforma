<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tenders;
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

    protected $fillable = [
        'querysable_id',
        'querysable_type',
        'company_id',
        'question',
        'answer',
        'user_id',
        'status',
        'user_answer_id',
        'visible'
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

    public function queryWallProjectUser() {
        return Tenders::find($this->querysable_id)->project->user_id;
    }
}
