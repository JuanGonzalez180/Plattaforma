<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use App\Models\TypesEntity;
use Illuminate\Support\Str as Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->name;
        
        $arrayUser = [
            'username' => Str::slug($this->faker->name),
            'name' => 'user',
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'admin' => User::USER_REGULAR,
        ];

        $user = User::create( $arrayUser );

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'type_entity_id' => $this->faker->randomElement([6,7,8,9]),
            'nit' => $this->faker->unique()->numerify('######'),
            'country_code' => 'CO',
            'web' => 'www.'.str_replace(" ", "_", $name).'.com',
            'status' => Company::COMPANY_APPROVED,
            'user_id' => $user->id,
        ];
    }
}
