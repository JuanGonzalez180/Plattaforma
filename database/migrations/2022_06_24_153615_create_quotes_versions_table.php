<?php

use App\Models\QuotesVersions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotesVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotes_versions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('quotes_id')->unsigned();
            $table->string('adenda', 1000);
            $table->integer('price')->unsigned();
            $table->string('status')->default(QuotesVersions::QUOTATION_CREATED);
            $table->string('date')->nullable();
            $table->string('hour')->nullable();
            $table->string('close')->default(QuotesVersions::QUOTATION_CLOSED_BLANK);

            $table->timestamps();

            $table->foreign('quotes_id')->references('id')->on('quotes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotes_versions');
    }
}
