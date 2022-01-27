<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersToken extends Model
{
    use HasFactory;

    const TYPE_FIREBASE = 'firebase';

    protected $fillable = [
        'type',
        'token',
        'user_id',
        'platform',
        'device',
        'version'
    ];

    public function isFirebase(){
        return $this->type == UsersToken::TYPE_FIREBASE;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
