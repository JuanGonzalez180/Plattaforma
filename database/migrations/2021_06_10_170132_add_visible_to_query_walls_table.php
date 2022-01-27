<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\QueryWall;

class AddVisibleToQueryWallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_walls', function (Blueprint $table) {
            $table->string('visible')
                ->default(QueryWall::QUERYWALL_VISIBLE)
                ->after('status');
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
