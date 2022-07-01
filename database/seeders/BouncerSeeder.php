<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Channel;
use App\Models\Account;
use App\Models\User;
use App\Models\Role;
// use App\Models\Permission;

use App\Models\Taxonomy;
use App\Models\Audit;
// use App\Models\Error;

use App\Models\Media;
use App\Models\Campaign;


use App\Models\Setting;
use App\Models\Tag;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\NotificationType;
// use App\Models\NotificationRecipient;

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
        Bouncer::allow('superadmin')->everything();
        Bouncer::allow('coder')->everything();

        $default_actions = ['index', 'create', 'edit', 'show', 'delete', 'status', 'audit'];

        // Bouncer::scope()->to($platform_master->id);
        
        // Bouncer::allow($role)->to(['list', 'show', 'create'], Taxonomy::class);
        
        $roles = ['support', 'commercial', 'designer', 'developer'];

        foreach ($roles as $key => $role)
        {
            Bouncer::allow($role)->to(['index', 'show',], Audit::class);
        }

        // Editor

        $roles = ['visitor'];

        $default_actions = ['index', 'show'];

        foreach ($roles as $key => $role)
        {
            Bouncer::allow($role)->to(['index', 'show',], Audit::class);
        }

        // Bouncer::allow('technical-support')->to(['index', 'show',], Error::class);
        


        // // Administrativo

        // Bouncer::allow('administrative')->to($default_actions, Client::class);

        // // Marketing

        // Bouncer::allow('marketing')->to($default_actions, Client::class);
        // Bouncer::allow('marketing')->to($default_actions, Post::class);
        // Bouncer::allow('marketing')->to($default_actions, NotificationType::class);



        // Bouncer::allow('admin')->everything();
        // Bouncer::forbid('admin')->toManage(User::class);

        // Bouncer::allow('support')->to('create', Plan::class);
        // Bouncer::allow('support')->toOwn(Plan::class);



        // Bouncer::ability([
        //     'title' => 'Crear usuarios',
        //     'name' => 'user-create'
        // ])->save();
        // Bouncer::allow('developer')->to('user-create');

        // $platform_client = Taxonomy::getFirstData('system', 'platform', 'client');

        // Bouncer::scope()->to($platform_client->id);

        // Bouncer::allow('owner')->everything();
        
        // Bouncer::allow('admin')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-content')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-course')->to(['index', 'show',], Post::class);
        // Bouncer::allow('admin-user')->to(['index', 'show',], Post::class);
        // Bouncer::allow('supervisor')->to(['index', 'show',], Post::class);


        // $platform_user = Taxonomy::getFirstData('system', 'platform', 'user');

        // Bouncer::scope()->to($platform_user->id);

        // Bouncer::allow('user')->to(['index', 'show',], Feature::class);


        // // Bouncer::scope()->to(PE)->onlyRelations()->dontScopeRoleAbilities();

        // $user = User::find(1);
        // $user->assign('superadmin');
        
        // $user = User::find(2);
        // $user->assign('superadmin');
    }
}
