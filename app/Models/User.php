<?php

namespace App\Models;

use App\Models\Blog;
use App\Models\Image;
use App\Models\Company;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Billable;

    const USER_VERIFIED = '1';
    const USER_NO_VERIFIED = '0';

    const USER_VALIDATED = '1';
    const USER_NO_VALIDATED = '0';

    const USER_ADMIN = 'true';
    const USER_REGULAR = 'false';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'code',
        'code_time',
        'password',
        'remember_token',
        'verification_token',
    ];

    public function setNameAttribute($value){
        $this->attributes['name'] = strtolower($value);
    }

    public function getNameAttribute($value){
        return ucfirst($value);
    }

    public function setEmailAttribute($value){
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

    public function isVerified(){
        return $this->verified == User::USER_VERIFIED;
    }

    public function isValidated(){
        return $this->validated == User::USER_VALIDATED;
    }

    public function isAdmin(){
        return $this->admin == User::USER_ADMIN;
    }

    public static function generateVerificationToken(){
        return Str::random(40);
    }

    public function blogs(){
        return $this->hasMany(Blog::class);
    }

    public function company(){
        return $this->hasMany(Company::class);
    }

    // Relacion uno a uno polimorfica
    public function image(){
        return $this->morphOne(Image::class, 'imageable');
    }
}
