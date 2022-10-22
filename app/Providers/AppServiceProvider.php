<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
// use Laravel\Passport\Passport;
use App\Models\Role;
use Carbon\Carbon;
use Bouncer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('es');
        //Bouncer::cache();
        // Passport::loadKeysFrom(base_path(config('passport.key_path')));
        Bouncer::useRoleModel(Role::class);

        // DB::listen(function($query) {
        //     File::append(
        //         storage_path('/logs/query.log'),
        //         $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
        //    );
        // });
    }
}
