<?php

namespace App\Models;

use App\Models\Blog;
use App\Models\Image;
use App\Models\Company;
use App\Models\Team;
use App\Models\Interests;
use App\Models\Notifications;
use App\Models\UsersToken;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use App\Transformers\UserTransformer;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Billable;

    const USER_VERIFIED     = '1';
    const USER_NO_VERIFIED  = '0';

    const USER_VALIDATED    = '1';
    const USER_NO_VALIDATED = '0';

    const USER_ADMIN        = 'true';
    const USER_REGULAR      = 'false';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    const USER_TRANSFORMER = UserTransformer::class;

    protected $fillable = [
        'username',
        'name',
        'lastname',
        'email',
        'password',
        'verified',
        'verification_token',
        'validated',
        'admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'stripe_id',
        'code',
        'code_time',
        'password',
        'remember_token',
        'verification_token',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isVerified()
    {
        return $this->verified == User::USER_VERIFIED;
    }

    public function isValidated()
    {
        return $this->validated == User::USER_VALIDATED;
    }

    public function isAdmin()
    {
        return $this->admin == User::USER_ADMIN;
    }

    public function isAdminFrontEnd()
    {
        return count($this->company) && $this->company[0];
    }

    public function userType()
    {
        if (count($this->company) && $this->company[0]) {
            return $this->company[0]->type_entity->type->slug;
        } elseif ($this->team) {
            return $this->team->company->type_entity->type->slug;
        }
        return '';
    }

    public function companyClass()
    {
        if (count($this->company) && $this->company[0]) {
            $company = $this->company[0];
            return $company;
        } elseif ($this->team) {
            return $this->team->company;
        }

        return 0;
    }

    public function companyId()
    {
        if (count($this->company) && $this->company[0]) {
            $company = $this->company[0];
            return $company->id;
        } elseif ($this->team) {
            return $this->team->company->id;
        }

        return 0;
    }

    public function companyFull()
    {
        if (count($this->company) && $this->company[0]) {
            $company = $this->company[0];
            return $company;
        } elseif ($this->team) {
            return $this->team->company;
        }

        return 0;
    }

    public function companyImg()
    {
        if (count($this->company) && $this->company[0]) {
            return ($this->company[0]->image) ? $this->company[0]->url  : null;
        } elseif ($this->team) {
            return ($this->team->company->image)? $this->team->company->image->url : null;
        }

        return 0;
    }

    public function companyName()
    {
        if (count($this->company) && $this->company[0]) {
            $company = $this->company[0];
            return $company->name;
        } elseif ($this->team) {
            return $this->team->company->name;
        }

        return 0;
    }

    public static function generateVerificationToken()
    {
        return Str::random(40);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function tokens()
    {
        return $this->hasMany(UsersToken::class);
    }

    public function company()
    {
        return $this->hasMany(Company::class);
    }

    //Relacion uno a uno
    public function team()
    {
        return $this->hasOne(Team::class);
    }

    // Relacion uno a uno polimorfica
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    // Relacion
    public function notifications()
    {
        return $this->hasMany(Notifications::class);
    }

    // Relacion
    public function interests()
    {
        return $this->hasMany(Interests::class);
    }

    //Nombre completo
    public function fullName()
    {
        $fullName = (is_null($this->lastname) || $this->lastname == '')
            ? $this->name
            : $this->name . " " . $this->lastname;

        return $fullName;
    }

    public function nameResponsable()
    {
        $fullName = (is_null($this->lastname) || $this->lastname == '')
            ? $this->name
            : $this->name . " " . $this->lastname;

        if (!$fullName && $this->team()) {
            $fullName = $this->email . "<br><span class='badge badge-warning'>(No ha completado el registro)</span>";
        }

        return $fullName;
    }

    public function getAdminUser()
    {
        return Company::where('user_id',$this->id)->exists();
    }
}
