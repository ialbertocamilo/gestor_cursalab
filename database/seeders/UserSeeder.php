<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\User;

use Khsing\World\Models\Country;

use Illuminate\Database\Seeder;
use Bouncer;

use Illuminate\Database\Eloquent\Factories\Sequence;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $platform_master = Taxonomy::getFirstData('system', 'platform', 'master');

        // Bouncer::scope()->to($platform_master->id);

        $password = env('TEST_PASS', 'Cursalab');


        $male = Taxonomy::getFirstData('user', 'gender', 'male');
        $cursalab = Taxonomy::getFirstData('user', 'type', 'cursalab');
        $employee = Taxonomy::getFirstData('user', 'type', 'employee');
        $document_type = Taxonomy::getFirstData('user', 'document-type', 'dni');

        // $country = Country::where('active', ACTIVE)->where('code', 'pe')->first();
        // $genders = Taxonomy::where('group', 'user')->where('type', 'gender')->get();
        // $positions = Taxonomy::where('group', 'user')->where('type', 'job-position')->get();
        // $areas = Taxonomy::where('group', 'user')->where('type', 'area')->get();

        $user_1 = User::create([
            'name' => 'Deyvi',
            // 'alias' => 'Deyvi C',
            'lastname' => 'Chavarría',
            'email' => 'deyvi@cursalab.io',
            'password' => $password,
            'type_id' => $cursalab->id,
        ]);

        $user_1->assign(['super-user']);

        $user_2 = User::create([
            'name' => 'Rodrigo',
            // 'alias' => 'Rodrigo CF',
            'lastname' => 'Callañaupa',
            'email' => 'rodrigo@cursalab.io',
            'password' => $password,
            'type_id' => $cursalab->id,
        ]);

        $user_2->assign(['super-user']);

        User::factory(35)
            ->state(new Sequence(
                fn () => ['type_id' => $employee]
            ))
            // ->state(new Sequence(
            //     fn() => ['gender_id' => $genders->random()]
            // ))
            // ->state(new Sequence(
            //     fn() => ['country_id' => $country]
            // ))
            // ->state(new Sequence(
            //     fn() => ['job_position_id' => $positions->random()]
            // ))
            // ->state(new Sequence(
            //     fn() => ['area_id' => $areas->random()]
            // ))
            ->create();
    }
}
