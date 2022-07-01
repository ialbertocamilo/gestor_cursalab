<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = Course::get();

        $topic = Topic::create([
            "name" => "Seguridad - Parte I",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);

        $topic = Topic::create([
            "name" => "Pausas activas",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);

        $topic = Topic::create([
            "name" => "Autodiagnóstico financiero",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);

        $topic = Topic::create([
            "name" => "Módulo de cuidados en casa",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);

        $topic = Topic::create([
            "name" => "Introducción sobre el COVID-19",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);

        $topic = Topic::create([
            "name" => "Módulo de cuidados en el trabajo",
            "description" => "",
            "course_id" => $courses->random()->id,
            "active" => ACTIVE,
        ]);
    }
}
