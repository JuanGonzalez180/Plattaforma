<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesDropColumToAdvertising extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->dropForeign('advertisings_registration_payments_id_foreign');
            $table->dropColumn('registration_payments_id');
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
            $table->bigInteger('registration_payments_id')->after('advertisingable_type')->unsigned();
            $table->foreign('registration_payments_id')->references('id')->on('registration_payments');
        });
    }
}
