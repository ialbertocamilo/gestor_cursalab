<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlatformFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Platform::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $slug = $this->faker->unique()->domainWord();

        return [
            'subdomain' => $slug,
            'storage_name' => $slug,
            'database_name' => $slug,
            'max_active_users' => 0,
            // 'max_active_users' => $this->faker->randomNumber(1,true)*100,
            // 'email' => $this->faker->unique()->companyEmail,
            // 'dni' => $this->faker->unique()->randomNumber(8,true),
            // 'company' => $this->faker->company,
            // 'ruc' => $this->faker->randomNumber(8,true),
            // 'phone' => $this->faker->phoneNumber,
            // 'telephone' => $this->faker->e164PhoneNumber,
            // 'registered_at' => now(),
            // 'active' => ACTIVE,
        ];
    }

}
