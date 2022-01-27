<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToAdvertising extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->string('start_date')->after('name')->nullable();
            $table->string('start_time')->after('start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('start_time');
        });
    }
}
