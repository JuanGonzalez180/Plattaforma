<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemsToQueryWallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_walls', function (Blueprint $table) {
            $table->bigInteger('querysable_id')->unsigned()->after('id');
            $table->string('querysable_type')->after('querysable_id');
            $table->bigInteger('company_id')->unsigned()->after('querysable_type');

            $table->string('answer', 1000)->nullable()->change();
            
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
        Schema::table('query_walls', function (Blueprint $table) {
            //
        });
    }
}
