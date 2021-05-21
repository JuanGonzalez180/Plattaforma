<?php

namespace Database\Factories;

use App\Models\Brands;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brands::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomElement([1,2,3]),
            'company_id' => $this->faker->randomElement([1,2,3]),
            'name' => ucwords($this->faker->unique()->name),
            'status' => $this->faker->randomElement([ Brands::BRAND_ENABLED , Brands::BRAND_DISABLED]),
        ];
    }
}
