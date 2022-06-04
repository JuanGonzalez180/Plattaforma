<?php

use App\Models\TendersVersions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsClosedTendersVersions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders_versions', function (Blueprint $table)
        {
            $table->string('close')
                ->after('hour')
                ->default(TendersVersions::LICITACION_CLOSED_BLANK);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders_versions', function (Blueprint $table)
        {
            $table->dropColumn('close');
        });
    }
}
