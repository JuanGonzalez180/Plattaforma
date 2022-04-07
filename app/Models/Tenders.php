<?php

namespace App\Models;

use JWTAuth;
use App\Models\Tags;
use App\Models\User;
use App\Models\Company;
use App\Models\Remarks;
use App\Models\Projects;
use App\Models\Interests;
use App\Models\QueryWall;
use App\Models\Advertisings;
use App\Models\Notifications;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\TendersTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenders extends Model
{
    use HasFactory;

    public $transformer = TendersTransformer::class;

    const TYPE_PUBLIC   = 'Publico';
    const TYPE_PRIVATE  = 'Privado';

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'company_id',
        'user_id',
        'type'
    ];

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function project()
    {
        return $this->belongsTo(Projects::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacion uno a muchos polimorfica
    public function querywalls()
    {
        return $this->morphMany(QueryWall::class, 'querysable');
    }

    // Nuevo
    public function tenderCategories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tendersVersion()
    {
        return $this->hasMany(TendersVersions::class);
    }

    // Relacion uno a muchos polimorfica
    public function advertisings()
    {
        return $this->morphMany(Advertisings::class, 'advertisingable');
    }

    public function tendersVersionLast()
    {
        if (count($this->tendersVersion) && $this->tendersVersion[0]) {
            return $this->tendersVersion[count($this->tendersVersion) - 1];
        }

        return [];
    }

    public function tendersVersionLastPublishTags()
    {
        $tenderVersionLastPublish = $this->tendersVersionLastPublish();
        if ($tenderVersionLastPublish) {
            $tags = Tags::where('tagsable_id', $tenderVersionLastPublish->id)
                ->where('tagsable_type', TendersVersions::class)
                ->orderBy('name', 'asc')
                ->pluck('name');

            return $tags;
        }
        return [];
    }

    public function tendersVersionLastPublish()
    {
        $tenderPublish = $this->tendersVersion
            ->where('status', '<>', TendersVersions::LICITACION_CREATED)
            ->sortBy([['created_at', 'desc']]);

        if ($tenderPublish && $tenderPublish->count()) {
            return $tenderPublish->first();
        }

        return [];
    }

    public function tenderCompanies()
    {
        return $this->hasMany(TendersCompanies::class, 'tender_id');
    }

    public function isWinner()
    {
        $tenderVersion = TendersCompanies::where('tender_id', $this->id)
            ->where('winner', TendersCompanies::WINNER_TRUE)
            ->exists();

        return $tenderVersion;
    }

    // Relacion uno a muchos polimorfica
    public function notifications()
    {
        return $this->morphMany(Notifications::class, 'notificationsable');
    }

    // Relacion uno a muchos polimorfica
    public function remarks()
    {
        return $this->morphMany(Remarks::class, 'remarksable');
    }

    // Relacion uno a muchos polimorfica
    public function interests()
    {
        return $this->morphMany(Interests::class, 'interestsable');
    }

    public function tenderStatusUser()
    {
        $status = Tenders::select('tenders_companies.status')
            ->where('tenders.id', $this->id)
            ->join('tenders_companies', 'tenders_companies.tender_id', '=', 'tenders.id')
            ->where('tenders_companies.company_id', $this->validateUser()->companyId())
            ->value('tenders_companies.status');

        return $status;
    }
}
