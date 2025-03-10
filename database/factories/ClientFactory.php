<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->companyEmail(),
            'dni' => $this->faker->unique()->randomNumber(8,true),
            'company' => $this->faker->company(),
            'ruc' => $this->faker->randomNumber(8,true),
            'phone' => $this->faker->phoneNumber(),
            'telephone' => $this->faker->e164PhoneNumber(),
            // 'registered_at' => now(),
            'active' => ACTIVE,
        ];
    }

}
