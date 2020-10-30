<?php

namespace App\Models;

use App\Messages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id',
        'company_id_receive',
        'user_id',
        'user_id_receive',
        'type',
        'type_id',
        'date',
        'date_update'
    ];

    public function messages(){
        return $this->hasMany(Messages::class);
    }
}
