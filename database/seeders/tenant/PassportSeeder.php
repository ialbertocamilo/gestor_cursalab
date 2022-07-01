<?php

namespace Database\Seeders\Tenant;

use DB;
use Illuminate\Database\Seeder;
// use Silber\Bouncer\Database\Role;

use Laravel\Passport\ClientRepository;

class PassportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        info('PassportSeeder Tenant');

        try {
            
            $client = new ClientRepository();

            $client->createPasswordGrantClient(null, 'Default password grant client', 'https://cursalab.io');
            $client->createPersonalAccessClient(null, 'Default personal access client', 'https://cursalab.io');

        } catch ( \Exception $e) {
            info($e);
        }
    }
}
