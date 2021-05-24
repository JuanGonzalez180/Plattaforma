<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Blog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => ucwords($this->faker->unique()->name),
            'description_short' => ucwords($this->faker->unique()->name),
            'description' => ucwords($this->faker->unique()->text),
            'status' => $this->faker->randomElement([ Blog::BLOG_ERASER , Blog::BLOG_PUBLISH]),
            'user_id' => $this->faker->randomElement([1,2,3]),
            'company_id' => $this->faker->randomElement([1,2,3])
        ];
    }
}
