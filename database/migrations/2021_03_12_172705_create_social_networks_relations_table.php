<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialNetworksRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_networks_relations', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('socialable_id')->unsigned();
            $table->string('socialable_type');

            $table->string('link');
            
            $table->bigInteger('social_networks_id')->unsigned();
            $table->foreign('social_networks_id')->references('id')->on('social_networks')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_networks_relations');
    }
}
