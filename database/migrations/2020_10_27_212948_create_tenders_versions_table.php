<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTendersVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenders_versions', function (Blueprint $table) {
            $table->id();
            $table->string('adenda', 1000);
            $table->bigInteger('licitacion_id')->unsigned();
            $table->integer('precio')->unsigned();
            $table->integer('numero')->unsigned();
            $table->string('unique_id');
            $table->string('date_start');
            $table->string('date_end');
            $table->string('date');
            $table->string('date_update');
            $table->timestamps();

            $table->foreign('licitacion_id')->references('id')->on('tenders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenders_versions');
    }
}
