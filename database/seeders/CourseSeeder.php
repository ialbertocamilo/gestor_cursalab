<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $course = Course::create([
           "name" => "Introducción al Curso",
           "description" => "",
           "active" => ACTIVE,
        ]);
        $course->schools()->sync([1]);

        $course = Course::create([
            "name" => "Seguridad y salud en el trabajo - Parte I",
            "description" => "",
            "active" => ACTIVE,
        ]);
        $course->schools()->sync([1]);

        $course = Course::create([
            "name" => "Seguridad y salud en el trabajo - Parte II",
            "description" => "",
            "active" => ACTIVE,
        ]);
        $course->schools()->sync([2]);

        $course = Course::create([
            "name" => "Curso de Prevención del COVID-19",
            "description" => "",
            "active" => ACTIVE,
        ]);
        $course->schools()->sync([2]);
    }
}
