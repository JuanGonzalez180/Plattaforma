<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Type;
use App\Models\TypesEntity;
use App\Models\User;
use App\Models\PaymentPlatform;
use App\Models\Currency;
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
            'name' => 'Juan Gonzalez', 
            'username' => 'juangon', 
            'email' => 'juan.gonzalez@incdustry.com', 
            'password' => bcrypt('Cambiame123'), 
            'verified' => User::USER_VERIFIED, 
            'admin' => User::USER_ADMIN
        ]);

        Type::create([
            'name' => 'Demanda'
        ]);

        Type::create([
            'name' => 'Oferta'
        ]);

        Country::create([
            'name' => 'Panamá',
            'slug' => 'panama',
            'alpha2Code' => 'PA'
        ]);
        
        $typesEntity = ['Constructora', 'Promotoras', 'Arquitectura', 'Arquitectura de interiores', 'Ingeniería'];
        foreach ($typesEntity as $key => $value) {
            $slug = strtolower($value);
            $slug = preg_replace('/[^[:alnum:]]/', ' ', $slug);
            $slug = preg_replace('/[[:space:]]+/', '-', $slug);

            TypesEntity::create([
                'type_id' => 1,
                'name' => $value,
                'slug' => trim($slug),
                'status' => TypesEntity::ENTITY_PUBLISH,
            ]);
        }

        $typesEntityOferta = ['Proveedores & Productos', 'Contratistas & Servicios', 'Seguros', 'Logística'];
        foreach ($typesEntityOferta as $key => $value) {
            $slug = strtolower($value);
            $slug = preg_replace('/[^[:alnum:]]/', ' ', $slug);
            $slug = preg_replace('/[[:space:]]+/', '-', $slug);

            TypesEntity::create([
                'type_id' => 2,
                'name' => $value,
                'slug' => trim($slug),
                'status' => TypesEntity::ENTITY_PUBLISH,
            ]);
        }
        

        //Payment
        PaymentPlatform::create([
            'name' => 'Stripe'
        ]);

        // Currency
        Currency::create([
            'iso' => 'usd'
        ]);
    }
}
