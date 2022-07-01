<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

use Database\Seeders\Tenant\PassportSeeder;
use Database\Seeders\Tenant\ModuleSeeder;
use Database\Seeders\Tenant\BouncerSeeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        info('TenantDatabaseSeeder Tenant');

        $this->call(PassportSeeder::class);
        $this->call(BouncerSeeder::class);

        // $this->call(CriteriaSeeder::class);
        $this->call(ModuleSeeder::class);
    }
}
