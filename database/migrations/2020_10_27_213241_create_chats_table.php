<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('company_id_receive')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('user_id_receive')->unsigned();
            $table->string('type');
            $table->bigInteger('type_id')->unsigned();
            $table->string('date');
            $table->string('date_update');
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
        Schema::dropIfExists('chats');
    }
}
