<?php

use App\Models\Remarks;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesToRemarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('remarks', function (Blueprint $table) {
            //
            $table->bigInteger('remarksable_id')->unsigned()->after('id');
            $table->string('remarksable_type')->after('remarksable_id');
            $table->dropColumn('type');
            $table->dropColumn('type_id');
            $table->dropColumn('date');
            $table->dropColumn('date_update');
            
            $table->bigInteger('company_id')->unsigned()->after('remarksable_type');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('remarks', function (Blueprint $table) {
            $table->dropColumn('remarksable_id');
            $table->dropColumn('remarksable_type');
            $table->string('type')->after('id');
            $table->bigInteger('type_id')->unsigned()->after('type');
            $table->string('date');
            $table->string('date_update');

            $table->dropForeign('company_id');
            $table->dropColumn('company_id');
        });
    }
}
