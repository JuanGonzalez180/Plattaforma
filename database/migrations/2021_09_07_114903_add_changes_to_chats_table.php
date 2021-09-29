<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            //
            $table->bigInteger('chatsable_id')->unsigned()->after('id');
            $table->string('chatsable_type')->after('chatsable_id');
            $table->dropColumn('type');
            $table->dropColumn('type_id');
            $table->dropColumn('date');
            $table->dropColumn('date_update');
            $table->string('name')->nullable()->change();
            $table->bigInteger('user_id')->nullable()->change();
            $table->bigInteger('user_id_receive')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            //
            $table->dropColumn('chatsable_id');
            $table->dropColumn('chatsable_type');
            $table->string('type')->after('id');
            $table->bigInteger('type_id')->unsigned()->after('type');
            $table->string('date');
            $table->string('date_update');
            $table->string('name')->nullable(false)->change();
            $table->bigInteger('user_id')->nullable(false)->change();
            $table->bigInteger('user_id_receive')->nullable(false)->change();
        });
    }
}
