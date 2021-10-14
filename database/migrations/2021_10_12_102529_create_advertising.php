<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertising extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('advertisingable_id')->unsigned();
            $table->string('advertisingable_type');
            $table->bigInteger('registration_payments_id')->unsigned();
            $table->bigInteger('plan_id')->unsigned();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->foreign('registration_payments_id')->references('id')->on('registration_payments');
            $table->foreign('plan_id')->references('id')->on('advertising_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertising');
    }
}
