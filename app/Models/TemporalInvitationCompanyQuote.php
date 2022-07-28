<?php

namespace App\Models;

use App\Models\Quotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporalInvitationCompanyQuote extends Model
{
    use HasFactory;

    protected $table = 'temporal_invitation_companies_quote';

    protected $fillable = [
        'quote_id',
        'email',
        'send'
    ];

    public function quote()
    {
        return $this->belongsTo(Quotes::class);
    }

    public function mailExists()
    {
        return User::where('email',$this->email)
            ->exists();
    }
}
