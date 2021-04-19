<?php

use App\Models\Projects;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('company_id')->unsigned();
            // $table->bigInteger('type_projects_id')->unsigned();

            $table->string('description')->nullable();
            $table->string('meters')->nullable();
            $table->string('date_start')->nullable();
            $table->string('date_end')->nullable();
            $table->string('status')->default(Projects::PROJECTS_ERASER);
            $table->timestamps();

            // $table->foreign('type_projects_id')->references('id')->on('type_projects');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
