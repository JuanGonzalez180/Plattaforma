<?php

use App\Models\Company;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToTerdersCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->bigInteger('user_id')
                ->unsigned()
                ->after('company_id');
        });

        foreach(TendersCompanies::all() as $TenderCompany) {
            $TenderCompany->user_id = $TenderCompany->company->user_id;
            $TenderCompany->save();
        }

        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            // $table->bigInteger('user_id')->nullable(false)->change();
        });
    }


    

    

    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('terders_companies', function (Blueprint $table) {
            //
        });
    }
}
