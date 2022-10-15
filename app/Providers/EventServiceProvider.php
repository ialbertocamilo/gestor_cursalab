<?php

namespace App\Providers;

use App\Models\Curso;
use App\Models\Topic;
use App\Models\Posteo;
use App\Models\Categoria;

use App\Observers\TopicObserver;
// use App\Observers\PosteoObserver;
// use App\Observers\CategoriaObserver;

use App\Observers\CourseObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Course::observe(CourseObserver::class);
        
        Topic::observe(TopicObserver::class);
        // Curso::observe(CursoObserver::class);
        // Categoria::observe(CategoriaObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
