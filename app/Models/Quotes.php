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
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\QuotesTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotes extends Model
{
    use HasFactory;

    public $transformer = QuotesTransformer::class;

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

    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class);
    // }

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
    public function quoteCategories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function quotesVersion()
    {
        return $this->hasMany(QuotesVersions::class);
    }

    public function advertisings()
    {
        return $this->morphMany(Advertisings::class, 'advertisingable');
    }

    public function quoteCompaniesIds()
    {
        return QuotesCompanies::where('quotes_id', $this->id)->pluck('company_id');
    }

    public function quotesVersionLast()
    {
        if (count($this->quotesVersion) && $this->quotesVersion[0]) {
            return $this->quotesVersion[count($this->quotesVersion) - 1];
        }

        return [];
    }

    public function quotesVersionLastPublishTags()
    {
        $quoteVersionLastPublish = $this->quotesVersionLastPublish();
        if ($quoteVersionLastPublish) {
            $tags = Tags::where('tagsable_id', $quoteVersionLastPublish->id)
                ->where('tagsable_type', QuotesVersions::class)
                ->orderBy('name', 'asc')
                ->pluck('name');

            return $tags;
        }
        return [];
    }

    public function quotesVersionLastPublish()
    {
        $quotePublish = $this->quotesVersion
            ->where('status', '<>', QuotesVersions::QUOTATION_CREATED)
            ->sortBy([['created_at', 'desc']]);

        if ($quotePublish && $quotePublish->count()) {
            return $quotePublish->first();
        }

        return [];
    }

    public function quoteCompanies()
    {
        return $this->hasMany(QuotesCompanies::class, 'quotes_id');
    }

    public function quoteCompaniesActive()
    {
        return QuotesCompanies::where('quotes_id', $this->id)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();
    }

    public function QuoteCompaniesId()
    {
        return QuotesCompanies::where('quotes_id', $this->id)
            ->pluck('company_id');
    }

    public function isWinner()
    {
        $quoteVersion = QuotesCompanies::where('quotes_id', $this->id)
            ->where('winner', QuotesCompanies::WINNER_TRUE)
            ->exists();

        return $quoteVersion;
    }

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

    public function quoteStatusUser()
    {
        $status = Quotes::select('quotes_companies.status')
            ->where('quotes.id', $this->id)
            ->join('quotes_companies', 'quotes_companies.quotes_id', '=', 'quotes.id')
            ->where('quotes_companies.company_id', $this->validateUser()->companyId())
            ->value('quotes_companies.status');

        return $status;
    }

    public function commissionedUsers()
    {
        return array_unique([$this->company->user->id, $this->user_id]);
    }

    public function participatingUsers()
    {
        $quotesCompanies = QuotesCompanies::where('quotes_id', $this->id)
            ->get();

        $users = [];
        foreach ($quotesCompanies as $company) {
            $users[] = $company->user_company_id;
            $users[] = $company->company->user->id;
        }

        return  $users;
    }

    public function QuoteParticipatingCompanyEmails()
    {
        $emails = [];

        $quotesCompanies = QuotesCompanies::where('quotes_id', $this->id)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();


        foreach ($quotesCompanies as $quoteCompany) {
            $emails[] = $quoteCompany->userCompany->email;
            $emails[] = $quoteCompany->company->user->email;
        }

        return array_unique($emails);
    }

    // *Devuelve los id/s de los usuarios participantes de la licitación.
    public function QuoteParticipatingCompanyIdUsers()
    {
        $ids = [];

        $quotesCompanies = QuotesCompanies::where('quotes_id', $this->id)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();


        foreach ($quotesCompanies as $quoteCompany) {
            $ids[] = $quoteCompany->userCompany->id;
            $ids[] = $quoteCompany->company->user->id;
        }

        return array_unique($ids);
    }

    // *Devuelve los id/s de administrador de la compañia y del ecargado de la licitación.
    public function QuoteAdminIdUsers()
    {
        $ids = [
            $this->company->user->id,
            $this->user->id,
        ];

        return array_unique($ids);
    }

    // *Devuelve los correos del administrador de la compañia y encargado de la licitación.
    public function QuoteAdminEmails()
    {
        $emails = [
            $this->company->user->email,
            $this->user->email,
        ];

        return array_unique($emails);
    }

    public function quotesCompaniesParticipating()
    {
        return QuotesCompanies::where('quotes_id', $this->id)
            ->where('status', QuotesCompanies::STATUS_PARTICIPATING)
            ->get();
    }


    public function quotesCompaniesParticipatingName()
    {
        $companies =  QuotesCompanies::select('companies.id','companies.name')->where('quotes_companies.quotes_id', $this->id)
            ->where('quotes_companies.status', QuotesCompanies::STATUS_PARTICIPATING)
            ->join('companies', 'companies.id', '=', 'quotes_companies.company_id')
            ->get();

        foreach ($companies as $value) {
            $value['image'] = $this->companyImage($value->id);
        }

        return $companies;
    }

    public function companyImage($company_id)
    {
        return Company::find($company_id)->image;
    }

    public function UserParticipateQuote()
    {
        $participate = [];
        $companies =  QuotesCompanies::where('quotes_companies.quotes_id', $this->id)
            ->where('quotes_companies.status', QuotesCompanies::STATUS_PARTICIPATING)
            ->join('companies', 'companies.id', '=', 'quotes_companies.company_id')
            ->get();

        foreach ($companies as $value)
        {
            $users['id']            = $value->company->user->id;
            $users['image']         = isset($value->company->user->image) ? $value->company->user->image->url: null ;
            $users['company']       = $value->company->name;
            $participate[]          = $users;

            $users['id']            = $value->quote->user->id;
            $users['image']         = isset($value->quote->user->image) ? $value->quote->user->image->url: null ;
            $users['company']       = $value->quote->company->name;
            $participate[]          = $users;

            if($value->company->user->id != $value->userCompany->id)
            {
                $users['id']            = $value->userCompany->id;
                $users['image']         = isset($value->userCompany->image) ? $value->userCompany->image->url: null ;
                $users['company']       = $value->userCompany->companyFull()->name;
                $participate[]          = $users;
            }
        }

        return $participate;
    }
}
