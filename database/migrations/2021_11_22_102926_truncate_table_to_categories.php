<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\CategoryProducts;
use App\Models\CategoryTenders;

class TruncateTableToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('category_products', function (Blueprint $table) {
            $table->dropForeign('category_products_category_id_foreign');
        });
        
        Schema::table('category_tenders', function (Blueprint $table) {
            $table->dropForeign('category_tenders_category_id_foreign');
        });
        
        CategoryProducts::truncate();
        CategoryTenders::truncate();
        
        Category::truncate();

        Schema::table('category_products', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::table('category_tenders', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::table('categories', function (Blueprint $table) {
            //
        });*/
    }
}
