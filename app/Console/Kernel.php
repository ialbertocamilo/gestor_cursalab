<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\MigrarUsuarios::class,

        Commands\reptot_data3::class,
        Commands\reptot_data2::class,
        Commands\reptot_data1::class,
        Commands\restablecer_resumenes::class,
//        Commands\primera_segunda_asistencia::class,
        Commands\reestablecer_ultima_sesion::class,
        Commands\ActualizarTablasResumen::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('buscar:incidencias {desde_back}')->dailyAt('06:00');

        // $schedule->command('reinicios:programados')->everyFifteenMinutes();
        $schedule->command('summary:reset-user-attempts')->everyFiveMinutes();
        // $schedule->command('delete:err_masivos')->dailyAt('03:00');

        $schedule->command('summary:update-data')->everyFifteenMinutes()
         ->withoutOverlapping();
        // $schedule->command('summary:update-data-v2')->everyFifteenMinutes();
        // $schedule->command('resumen:update_resumen_general')->everyFifteenMinutes();
        $schedule->command('notificaciones:enviar')->everyMinute();

        $schedule->command('quizzes:finish-summary-overdue')->everyTenMinutes();

        // Meetings
        $schedule->command('meeting:update-status')->hourly();
        $schedule->command('meeting:update-attendance-detail')->everyFiveMinutes();
        $schedule->command('meeting:verify-finish-status')->everyTenMinutes();
        $schedule->command('meeting:update-url-start')->everyTenMinutes();

        // // Accounts
        // $schedule->command('account:update-tokens')->monthly();

        $schedule->command('errores:eliminar-antiguos')->dailyAt('00:00');

        $schedule->command('report:users-quantity')->dailyAt('23:58');

        $schedule->command('mongo:save-data')->dailyAt('03:00');

        // Notifications

        $schedule->command('notifications:clear')->dailyAt('05:00');

        // Courses

        $schedule->command('courses:activate-deactivate')->everyFiveMinutes();

        // Criteria

        $schedule->command('criteria:check-empty')->everyThreeHours();

        $schedule->command('tokens:revoke-impersonation-access')->everyTenMinutes();
        $schedule->command('tokens:revoke-users-access')->everyFifteenMinutes();
        // $schedule->command('passport:purge --hours=1')->hourly();

        // Beneficios
        $schedule->command('beneficios:change-status')->dailyAt('00:00');
        $schedule->command('beneficios:notify-users')->dailyAt('00:30');
        $schedule->command('beneficios:email-segments')->everyFiveMinutes();
        //Checklist
        $schedule->command('update:checklist-summary-user')->hourly();
        //EMAILS
        $schedule->command('send:reminder-inactivate-course')->fridays()->at('23:05');
        $schedule->command('send:reminder-progress-course')->fridays()->at('23:00');
        $schedule->command('send:emails')->everyFiveMinutes();

        // Reports

        $schedule->command('reports:history')->weeklyOn(5, '04:30'); // Every friday

        //JARVIS
        $schedule->command('convert:multimedia-text')->everyTwoMinutes();
        $schedule->command('reset:attempts-jarvis')->monthlyOn(1, '00:10');
        //DC3
        $schedule->command('create:dc3')->everyFiveMinutes();
        //Courses in person
        $schedule->command('duplicate:assistance')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
