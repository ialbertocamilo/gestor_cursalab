<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        School::create([
          'name' => "Seguridad y salud en el trabajo",
          'description' => "",
          'active' => ACTIVE,
        ]);

        School::create([
            'name' => "Seguridad y salud en el trabajo II",
            'description' => "",
            'active' => ACTIVE,
        ]);
    }
}
