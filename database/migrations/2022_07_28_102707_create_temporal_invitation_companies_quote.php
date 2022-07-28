<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporalInvitationCompaniesQuote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporal_invitation_companies_quote', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quote_id')->unsigned();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('quotes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporal_invitation_companies_quote');
    }
}
