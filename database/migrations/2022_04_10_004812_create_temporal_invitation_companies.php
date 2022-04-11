<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporalInvitationCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporal_invitation_companies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tender_id')->unsigned();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporal_invitation_companies');
    }
}
