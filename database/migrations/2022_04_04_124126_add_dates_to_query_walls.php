<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatesToQueryWalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_walls', function (Blueprint $table) {
            $table->string('date_questions')->after('question')->nullable();
            $table->string('date_answer')->after('answer')->nullable();
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
            $table->dropColumn('date_questions');
            $table->dropColumn('date_answer');
        });
    }
}
