<?php

use App\Models\TendersVersions;
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

            $table->bigInteger('tenders_id')->unsigned();
            $table->string('adenda', 1000);
            $table->integer('price')->unsigned();
            $table->string('status')->default(TendersVersions::LICITACION_CREATED);
            $table->string('date')->nullable();
            $table->string('hour')->nullable();
            
            $table->timestamps();

            $table->foreign('tenders_id')->references('id')->on('tenders');
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
