<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
// use App\Models\Plan;
use App\Models\User;
use App\Models\Course;
use App\Models\School;
// use App\Models\Server;
use App\Models\Taxonomy;
// use App\Models\Platform;
// use App\Models\Role;
// use App\Models\Client;
// use App\Models\Permission;
// use App\Models\NotificationType;
// use App\Models\Payment;
// use App\Models\Price;
// use App\Models\Audit;
// use App\Models\Criterion;
// use App\Models\CriterionValue;
// use App\Models\Post;
// use App\Models\Feature;
// use App\Models\Ticket;
// use App\Models\Error;

use Bouncer;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bouncer::allow('cursalab')->everything();
        Bouncer::allow('developer')->everything();

        // $platform_client = Taxonomy::on('pgsql')->getFirstData('system', 'platform', 'client');
        $platform_client = Taxonomy::on('pgsql')->where('group', 'system')->where('type', 'platform')->where('code', 'web')->first();

        info($platform_client);

        Bouncer::scope()->to($platform_client->id);

        Bouncer::allow('owner')->everything();
        
        Bouncer::allow('admin')->to(['list', 'show',], User::class);
        Bouncer::allow('admin-content')->to(['list', 'show',], School::class);
        Bouncer::allow('admin-course')->to(['list', 'show',], Course::class);
        Bouncer::allow('admin-user')->to(['list', 'show',], User::class);
        // Bouncer::allow('supervisor')->to(['list', 'show',], Post::class);


        // $platform_user = Taxonomy::on('pgsql')->getFirstData('system', 'platform', 'user');
        $platform_user = Taxonomy::on('pgsql')->where('group', 'system')->where('type', 'platform')->where('code', 'app')->first();

        Bouncer::scope()->to($platform_user->id);

        // Bouncer::allow('user')->to(['list', 'show',], Feature::class);


        // // Bouncer::scope()->to(PE)->onlyRelations()->dontScopeRoleAbilities();

        // $user = User::find(1);
        // $user->assign('superadmin');
        
        // $user = User::find(2);
        // $user->assign('superadmin');
    }
}
