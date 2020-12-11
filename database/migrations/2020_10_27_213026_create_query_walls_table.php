<?php

use App\Models\QueryWall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueryWallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('query_walls', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('licitacion_id')->unsigned();
            $table->string('subject');
            $table->string('question', 1000);
            $table->string('answer', 1000);
            $table->bigInteger('user_id')->unsigned();
            $table->string('status')->default(QueryWall::QUERYWALL_ERASER);
            $table->string('date');
            $table->string('date_update');
            $table->timestamps();

            $table->foreign('licitacion_id')->references('id')->on('tenders');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('query_walls');
    }
}
