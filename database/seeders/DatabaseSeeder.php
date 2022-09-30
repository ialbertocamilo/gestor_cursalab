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
        // $this->call(TaxonomySeederSystem::class);
        // // $this->call(TaxonomySeederAppMenu::class);
        // // $this->call(TaxonomySeederNotification::class);
        // // $this->call(TaxonomySeederPriority::class);
        // // $this->call(TaxonomySeederMediaTypes::class);
        // $this->call(TaxonomySeederPoll::class);
        // $this->call(TaxonomySeederQuestionType::class);
        // $this->call(TaxonomySeederCourse::class);
        // $this->call(TaxonomySeederTopicEvaluationType::class);
        // $this->call(TaxonomySeederType::class);
//         $this->call(TaxonomySeederCourseType::class);
//         $this->call(TaxonomySeederSummarySource::class);
         $this->call(TaxonomySeederSegmentType::class);


        // $this->call(BouncerSeeder::class);
        // // $this->call(UserSeeder::class);

        // // Seeds for testing purposes

        // $this->call(TestSeeder::class);
//        $this->call(TestUserSeeder::class);
    }
}
