<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Platform;
use App\Models\Server;
use App\Models\Taxonomy;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Khsing\World\Models\Country;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $servers = Server::all();
        // $plans = Plan::all();

        $types = Taxonomy::where('group', 'server')->where('type', 'type')->get();
        $sources = Taxonomy::where('group', 'system')->where('type', 'source')->get();
        $sectors = Taxonomy::where('group', 'client')->where('type', 'sector')->get();
        $employees = Taxonomy::where('group', 'client')->where('type', 'employees')->get();

        $instance_id = $types->where('code', 'instance')->first()->id;
        $database_id = $types->where('code', 'database')->first()->id;
        $storage_id = $types->where('code', 'storage')->first()->id;
        $worker_id = $types->where('code', 'worker')->first()->id;

        $countries = Country::where('active', ACTIVE)->orderBy('name')->get();

        Client::factory(20)
            ->state(new Sequence(
                fn() => ['source_id' => $sources->random()]
            ))
            ->state(new Sequence(
                fn() => ['country_id' => $countries->random()]
            ))

            ->state(new Sequence(
                fn() => ['sector_id' => $sectors->random()]
            ))

            ->state(new Sequence(
                fn() => ['max_employee_id' => $employees->random()]
            ))
            // ->hasPlatform()
            ->has(
                Platform::factory()
                    ->count(1)
                    // ->state(new Sequence(
                    //     fn() => ['plan_id' => $plans->random()],
                    // ))
                    ->state(new Sequence(
                        fn() => ['instance_id' => $servers->where('type_id', $instance_id)->random()]
                    ))
                    ->state(new Sequence(
                        fn() => ['database_id' => $servers->where('type_id', $database_id)->random()]
                    ))
                    ->state(new Sequence(
                        fn() => ['storage_id' => $servers->where('type_id', $storage_id)->random()]
                    ))
                    ->state(new Sequence(
                        fn() => ['worker_id' => $servers->where('type_id', $worker_id)->random()]
                    ))
            )
            ->create();

        Client::factory(10)
            ->state(new Sequence(
                fn() => ['source_id' => $sources->random()]
            ))
            ->state(new Sequence(
                fn() => ['country_id' => $countries->random()]
            ))

            ->state(new Sequence(
                fn() => ['sector_id' => $sectors->random()]
            ))
            ->state(new Sequence(
                fn() => ['max_employee_id' => $employees->random()]
            ))
            ->create();
    }
}
