<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToInterestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interests', function (Blueprint $table) {
            //
            $table->bigInteger('interestsable_id')->unsigned()->after('id');
            $table->string('interestsable_type')->after('interestsable_id');
            $table->dropColumn('type');
            $table->dropColumn('type_id');
            $table->dropColumn('date');
            $table->dropColumn('date_update');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interests', function (Blueprint $table) {
            //
            $table->dropColumn('interestsable_id');
            $table->dropColumn('interestsable_type');
            $table->string('type')->after('id');
            $table->bigInteger('type_id')->unsigned()->after('type');
            $table->string('date');
            $table->string('date_update');
        });
    }
}
