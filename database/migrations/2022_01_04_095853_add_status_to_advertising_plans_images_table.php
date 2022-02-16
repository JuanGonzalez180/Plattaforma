<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AdvertisingPlansImages;

class AddStatusToAdvertisingPlansImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertising_plans_images', function (Blueprint $table) {
            $table->string('status')->after('images_advertising_plans_id')
                ->default(AdvertisingPlansImages::ADVER_PLAN_IMAGE_ERASER);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
