<?php

namespace App\Models;

use App\Chat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'date',
        'date_update'
    ];

    public function chats(){
        return $this->belongsTo(Chat::class);
    }
}
