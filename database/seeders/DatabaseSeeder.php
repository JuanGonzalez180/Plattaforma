<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Juan González', 
            'email' => 'juan.gonzalez@incdustry.com', 
            'password' => bcrypt('Cambiame123'), 
            'verified' => User::USER_VERIFIED, 
            'admin' => User::USER_ADMIN
        ]);
    }
}
