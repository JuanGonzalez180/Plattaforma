<?php

namespace App\Models;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\MessagesTransformer;

class Messages extends Model
{
    protected $guarded = [];

    use HasFactory;
    
    public $transformer = MessagesTransformer::class;

    protected $fillable = [
        'chat_id',
        'user_id',
        'viewed',
        'status',
        'message',
        'date',
        'date_update'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function chats(){
        return $this->belongsTo(Chat::class);
    }
}
