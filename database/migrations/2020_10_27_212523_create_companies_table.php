<?php

use App\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('type_entity_id')->unsigned();
            $table->integer('nit')->unsigned();
            $table->integer('country_id')->unsigned();
            $table->string('web');
            $table->string('image');
            $table->string('status')->default(Compant::COMPANY_CREATED);
            $table->integer('user_id')->unsigned();
            $table->string('date');
            $table->string('date_update');
            $table->timestamps();

            $table->foreign('type_entity_id')->references('id')->on('types_entities');
            $table->foreign('country_id')->references('id')->on('countries');
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
        Schema::dropIfExists('companies');
    }
}
