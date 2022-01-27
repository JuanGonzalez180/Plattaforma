<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisingPlansPaidImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertising_plans_paid_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('advertisings_id')->unsigned();
            $table->bigInteger('adver_plans_images_id')->unsigned();
            $table->timestamps();

            $table->foreign('advertisings_id')->references('id')->on('advertisings');
            $table->foreign('adver_plans_images_id')->references('id')->on('advertising_plans_images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertising_plans_paid_images');
    }
}
