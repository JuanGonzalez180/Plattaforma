<?php

namespace Database\Seeders;
use App\Models\Brands;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        Brands::create([
            'user_id' => 1,
            'name' => 'Sin Marca', 
            'status' => 'true'
        ]);
        */
        Brands::factory(20)->create();
    }
}
