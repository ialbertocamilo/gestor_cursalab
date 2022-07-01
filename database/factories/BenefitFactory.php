<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Benefit>
 */
class BenefitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = $this;

        return [
            'name' => $faker->faker->name(),
            'description' => $faker->faker->realText(),
            'stock' => random_int(1,100),
            'starts_at' => now(),
            'finishes_at' => now(),
            'active' => ACTIVE,
        ];
    }
}
