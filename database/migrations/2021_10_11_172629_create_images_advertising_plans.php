<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ImagesAdvertisingPlans;


class CreateImagesAdvertisingPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images_advertising_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('width')->default(0);
            $table->string('high')->default(0);
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images_advertising_plans');
    }
}
