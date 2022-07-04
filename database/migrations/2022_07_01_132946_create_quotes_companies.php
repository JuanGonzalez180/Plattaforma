<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\QuotesCompanies;

class CreateQuotesCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotes_companies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quotes_id')->unsigned();
            $table->bigInteger('company_id')->unsigned();
            $table->string('type')->default(QuotesCompanies::TYPE_INVITED);
            $table->string('status')->default(QuotesCompanies::STATUS_PARTICIPATING);
            $table->integer('price')->default(0);
            $table->string('commission')->default(0);
            $table->string('winner')->default(QuotesCompanies::WINNER_FALSE);
            $table->unsignedBigInteger('user_company_id')->nullable();
            $table->timestamps();
            
            $table->foreign('quotes_id')->references('id')->on('quotes');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_company_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotes_companies');
    }
}
