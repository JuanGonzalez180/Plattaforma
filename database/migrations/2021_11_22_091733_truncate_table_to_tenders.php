<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TendersCompanies;
use App\Models\TendersVersions;
use App\Models\CategoryTenders;
use App\Models\Tenders;
use App\Models\Proponents;

class TruncateTableToTenders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('tenders_versions', function (Blueprint $table) {
            $table->dropForeign('tenders_versions_tender_id_foreign');
        });

        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->dropForeign('tenders_companies_tender_id_foreign');
        });
        
        Schema::table('category_tenders', function (Blueprint $table) {
            $table->dropForeign('category_tenders_tenders_id_foreign');
        });
        
        Schema::table('proponents', function (Blueprint $table) {
            $table->dropForeign('proponents_licitacion_id_foreign');
        });
        
        TendersVersions::truncate();
        TendersCompanies::truncate();
        CategoryTenders::truncate();
        Proponents::truncate();

        Tenders::truncate();

        Schema::table('tenders_versions', function (Blueprint $table) {
            $table->foreign('tenders_id')->references('id')->on('tenders');
        });

        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->foreign('tender_id')->references('id')->on('tenders');
        });

        Schema::table('category_tenders', function (Blueprint $table) {
            $table->foreign('tenders_id')->references('id')->on('tenders');
        });

        Schema::table('proponents', function (Blueprint $table) {
            $table->foreign('licitacion_id')->references('id')->on('tenders');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::table('tenders_versions', function (Blueprint $table) {
            $table->dropForeign('tenders_versions_tender_id_foreign');
        });

        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->dropForeign('tenders_companies_tender_id_foreign');
        });
        
        Schema::table('category_tenders', function (Blueprint $table) {
            $table->dropForeign('category_tenders_tenders_id_foreign');
        });
        
        Schema::table('proponents', function (Blueprint $table) {
            $table->dropForeign('proponents_licitacion_id_foreign');
        });
        
        TendersVersions::truncate();
        TendersCompanies::truncate();
        CategoryTenders::truncate();
        Proponents::truncate();

        Tenders::truncate();

        Schema::table('tenders_versions', function (Blueprint $table) {
            $table->foreign('tenders_id')->references('id')->on('tenders');
        });

        Schema::table('tenders_companies', function (Blueprint $table) {
            $table->foreign('tenders_id')->references('id')->on('tenders');
        });

        Schema::table('category_tenders', function (Blueprint $table) {
            $table->foreign('tenders_id')->references('id')->on('tenders');
        });

        Schema::table('proponents', function (Blueprint $table) {
            $table->foreign('licitacion_id')->references('id')->on('tenders');
        });*/
    }
}
