<?php

namespace Database\Seeders;

use App\Models\Benefit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BenefitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

//        Benefit::factory()
//            ->count(50)
//            ->create();

        Benefit::create([
           'name' => Str::random(20),
           'description' => Str::random(100),
           'stock' => random_int(1,100),
           'starts_at' => now(),
           'finishes_at' => now(),
           'active' => ACTIVE,
        ]);

        Benefit::create([
            'name' => Str::random(20),
            'description' => Str::random(100),
            'stock' => random_int(1,100),
            'starts_at' => now(),
            'finishes_at' => now(),
            'active' => ACTIVE,
        ]);

        Benefit::create([
            'name' => Str::random(20),
            'description' => Str::random(100),
            'stock' => random_int(1,100),
            'starts_at' => now(),
            'finishes_at' => now(),
            'active' => ACTIVE,
        ]);

        Benefit::create([
            'name' => Str::random(20),
            'description' => Str::random(100),
            'stock' => random_int(1,100),
            'starts_at' => now(),
            'finishes_at' => now(),
            'active' => ACTIVE,
        ]);

        Benefit::create([
            'name' => Str::random(20),
            'description' => Str::random(100),
            'stock' => random_int(1,100),
            'starts_at' => now(),
            'finishes_at' => now(),
            'active' => ACTIVE,
        ]);
    }
}
