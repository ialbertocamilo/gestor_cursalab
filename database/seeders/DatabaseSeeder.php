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
        $this->call(TaxonomySeederAppMenu::class);
        $this->call(TaxonomySeederNotification::class);
        $this->call(TaxonomySeederPriority::class);
        $this->call(TaxonomySeederMediaTypes::class);
        // $this->call(TaxonomySeederClientEmployees::class);
        // $this->call(TaxonomySeederFeature::class);
        // $this->call(TaxonomySeederFeatureInterval::class);
        // $this->call(TaxonomySeederFeatureType::class);
        // $this->call(TaxonomySeederServer::class);

        // $this->call(TaxonomySeederClientEmployees::class);
        // $this->call(TaxonomySeederSector::class);
        $this->call(TaxonomySeederQuestionType::class);

        $this->call(BouncerSeeder::class);
        $this->call(UserSeeder::class);
        // $this->call(ClientSeeder::class);

        $this->call(SchoolSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(TopicSeeder::class);

        // $this->call(GamingSeeder::class);

        // $this->call(AccountSeeder::class);
        // $this->call(PostSeeder::class);
        // $this->call(NotificationTypeSeeder::class);
    }
}
