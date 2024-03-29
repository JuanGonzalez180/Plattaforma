<?php

use App\Models\TypeProject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_projects', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->string('description', 1000);
            $table->bigInteger('parent_id')->nullable()->unsigned();
            $table->string('status')->default(TypeProject::TYPEPROJECT_ERASER);
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
        Schema::dropIfExists('type_projects');
    }
}
