<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToRegistrationPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registration_payments', function (Blueprint $table) {
            $table->bigInteger('paymentsable_id')->after('type')->unsigned();
            $table->string('paymentsable_type')->after('paymentsable_id');
            $table->bigInteger('company_id')->after('id')->unsigned();

            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_payments', function (Blueprint $table) {
            $table->dropColumn('paymentsable_id');
            $table->dropColumn('paymentsable_type');
            $table->dropColumn('company_id');
        });
    }
}
