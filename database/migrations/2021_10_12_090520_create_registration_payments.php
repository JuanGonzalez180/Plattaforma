<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RegistrationPayments;

class CreateRegistrationPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();
            $table->string('price')->default(0);
            $table->string('type')->default(RegistrationPayments::TYPE_STRIPE);
            $table->string('reference_payments')->nullable();
            $table->string('status')->default(RegistrationPayments::REGISTRATION_PENDING);
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
        Schema::dropIfExists('registration_payments');
    }
}
