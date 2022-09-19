<?php

namespace App\Models;

use App\Models\Team;
use App\Models\Quotes;
use App\Models\Tenders;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OCILob;

class TemporalRecomendation extends Model
{
    use HasFactory;

    protected $table = 'temporal_recommendation';

    protected $fillable = [
        'company_id',
        'modelsable_id',
        'modelsable_type',
    ];

    // protected $hidden = [
    //     'modelsable_id',
    //     'modelsable_type',
    // ];

    public function filesable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tender()
    {
        return Tenders::find($this->modelsable_id);
    }

    public function tenderExist()
    {
        return Tenders::where('id',$this->modelsable_id)->exists();
    }

    public function quote()
    {
        return Quotes::find($this->modelsable_id);
    }

    public function quoteExist()
    {
        return Quotes::where('id',$this->modelsable_id)->exists();
    }

    public function emails()
    {
        $teams = Team::select('users.email')
            ->where('company_id', $this->company_id)
            ->where('status', 'Aprobado')
            ->join('users', 'users.id', '=', 'teams.user_id')
            ->pluck('users.email')
            ->all();

        $email = array_merge([$this->company->user->email], $teams);

        return $email;
    }
}