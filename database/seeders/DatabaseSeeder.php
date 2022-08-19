<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // When taxonomy is not empty, stop method execution
        //dd(bcrypt('12345')); return;
        if (Taxonomy::count() == 0) {
            // $this->call(WorldTablesSeeder::class);

            $this->call(TaxonomySeederSystem::class);
            // $this->call(TaxonomySeederAppMenu::class);
            // $this->call(TaxonomySeederNotification::class);
            // $this->call(TaxonomySeederPriority::class);
            // $this->call(TaxonomySeederMediaTypes::class);
            $this->call(TaxonomySeederPoll::class);
            $this->call(TaxonomySeederQuestionType::class);
            $this->call(TaxonomySeederCourse::class);
            $this->call(TaxonomySeederTopicEvaluationType::class);
            $this->call(TaxonomySeederType::class);


            $this->call(BouncerSeeder::class);
            $this->call(UserSeeder::class);

            //        $this->call(SchoolSeeder::class);
            //        $this->call(CourseSeeder::class);
            //        $this->call(TopicSeeder::class);

            // $this->call(AccountSeeder::class);
            // $this->call(PostSeeder::class);
            // $this->call(NotificationTypeSeeder::class);
        }

        // Seeds for testing purposes

        // $this->call(TestSeeder::class);
    }
}
