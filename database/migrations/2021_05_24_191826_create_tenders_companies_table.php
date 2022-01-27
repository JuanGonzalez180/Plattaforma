<?php

use App\Models\TendersCompanies;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTendersCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenders_companies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tender_id')->unsigned();
            $table->bigInteger('company_id')->unsigned();
            $table->string('type')->default(TendersCompanies::TYPE_INVITED);
            $table->string('status')->default(TendersCompanies::STATUS_PARTICIPATING);
            $table->integer('price')->default(0);
            $table->string('winner')->default(TendersCompanies::WINNER_FALSE);

            $table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenders_companies');
    }
}
