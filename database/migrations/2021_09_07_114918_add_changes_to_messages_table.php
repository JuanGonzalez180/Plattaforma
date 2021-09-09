<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            //
            $table->string('status')->nullable()->after('message');
            $table->boolean('viewed')->nullable()->default(0)->after('status');
            $table->dropColumn('date');
            $table->dropColumn('date_update');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            //
            $table->string('date');
            $table->string('date_update');
            $table->dropColumn('status');
            $table->dropColumn('viewed');
        });
    }
}
