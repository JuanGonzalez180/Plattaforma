<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisingPlansImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertising_plans_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('advertising_plans_id')->unsigned();
            $table->bigInteger('images_advertising_plans_id')->unsigned();
            $table->timestamps();

            $table->foreign('advertising_plans_id')->references('id')->on('advertising_plans');
            $table->foreign('images_advertising_plans_id')->references('id')->on('images_advertising_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertising_plans_images');
    }
}
