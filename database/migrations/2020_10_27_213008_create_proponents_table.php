<?php

use App\Models\Proponents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proponents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('licitacion_id')->unsigned();
            $table->string('type')->default(Proponents::TYPE_INVITED);
            $table->string('date_aceptacion');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('company_id')->unsigned();
            $table->integer('winner')->unsigned();
            $table->string('status')->default(Proponents::PROPONENTS_PARTICIPATING);
            $table->string('date');
            $table->string('date_update');
            $table->timestamps();

            $table->foreign('licitacion_id')->references('id')->on('tenders');
            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('proponents');
    }
}
