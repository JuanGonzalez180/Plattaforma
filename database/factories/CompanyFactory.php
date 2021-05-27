<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use App\Models\TypesEntity;
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
        return [
            'name' => ucfirst($name),
            'slug' => $name,
            'type_entity_id' => $this->faker->randomElement(TypesEntity::pluck('id')),
            'nit' => $this->faker->unique()->numerify('######'),
            'country_code' => 'CO',
            'web' => 'www.'.str_replace(" ", "_", $name).'.com',
            'status' => $this->faker->randomElement([ Company::COMPANY_CREATED , Company::COMPANY_APPROVED, Company::COMPANY_REJECTED ]),
            'user_id' => $this->faker->randomElement( User::pluck('id') ),
        ];
    }
}
