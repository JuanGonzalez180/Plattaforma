<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\QueryWall;

class AddColumnsTypeToQueryWalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_walls', function (Blueprint $table) {
            $table->string('type')
                ->after('visible')
                ->default(QueryWall::TYPE_QUERY);
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
            $table->dropColumn('type');
        });
    }
}
