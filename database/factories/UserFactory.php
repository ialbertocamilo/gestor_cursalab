<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $password = env('TEST_PASS', 'Cursalab');

        return [
            'name' => $this->faker->firstName(),
            'lastname' => $this->faker->lastname(),
            'surname' => $this->faker->lastname(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => $password, // password
            'remember_token' => Str::random(10),
            'birthdate' => $this->faker->dateTimeInInterval('-50 years', '+32 years')
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
