<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\Account;
use App\Models\Channel;
use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

use Khsing\World\Models\Country;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = User::whereRelation('type', 'code', 'client')->get();
        $companies = Taxonomy::where('group', 'account')->where('type', 'company')->get();
        $categories = Taxonomy::where('group', 'task')->where('type', 'category')->get();
        $types = Taxonomy::where('group', 'channel')->where('type', 'type')->get();

        $countries = Country::where('active', ACTIVE)->get();

        Account::factory(5)
            ->state(new Sequence(
                fn() => ['contact_id' => $clients->random()]
            ))
            ->state(new Sequence(
                fn() => ['company_id' => $companies->random()]
            ))
            ->state(new Sequence(
                fn() => ['country_id' => $countries->random()]
            ))
            // ->state(new Sequence(
            //     fn() => ['city_id' => $cities->random()]
            // ))
            ->has(
                Channel::factory()
                    ->count(5)
                    ->state(new Sequence(
                        fn() => ['type_id' => $types->random()],
                    ))
                    
            )
            ->create();

       
    }
}
