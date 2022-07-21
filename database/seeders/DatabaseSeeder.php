<?php

namespace Database\Seeders;

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
}
