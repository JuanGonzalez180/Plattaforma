<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteItemsToQueryWallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_walls', function (Blueprint $table) {
            $table->dropForeign('query_walls_licitacion_id_foreign');
            $table->dropColumn('tender_id');
            $table->dropColumn('subject');
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
