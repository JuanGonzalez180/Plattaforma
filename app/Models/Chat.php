<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Messages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ChatTransformer;

class Chat extends Model
{
    protected $guarded = [];
    
    use HasFactory;

    public $transformer = ChatTransformer::class;

    protected $fillable = [
        'name',
        'chatsable_id',
        'chatsable_type',
        'company_id',
        'company_id_receive',
        'user_id',
        'user_id_receive'
    ];

    protected $hidden = [
        'chatsable_id',
        'chatsable_type',
    ];

    public function userSend(){
        $user = User::findOrFail($this->user_id);
        return $user;
    }

    public function userReceive(){
        $user = User::findOrFail($this->user_id_receive);
        return $user;
    }

    public function companySend(){
        $company = Company::findOrFail($this->company_id);
        return $company;
    }

    public function companyReceive(){
        $company = Company::findOrFail($this->company_id_receive);
        return $company;
    }

    public function chatsable(){
        return $this->morphTo();
    }

    public function messages(){
        return $this->hasMany(Messages::class);
    }

    public function chatData(){
        if( $this->chatsable_type == Tenders::class ){
            return Tenders::find($this->chatsable_id);
        }
        return [];
    }
}
