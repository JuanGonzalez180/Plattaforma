<?php

namespace App\Models;

use App\Models\Tenders;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemporalInvitationCompany extends Model
{
    use HasFactory;

    protected $table = 'temporal_invitation_companies';

    protected $fillable = [
        'tender_id',
        'email',
        'send'
    ];

    public function tender()
    {
        return $this->belongsTo(Tenders::class);
    }
}
