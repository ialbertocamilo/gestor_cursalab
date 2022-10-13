<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
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

        $schedule->command('reinicios:programados')->everyMinute();
        // $schedule->command('delete:err_masivos')->dailyAt('03:00');

        // $schedule->command('resumen:update_resumen_general')->everyFifteenMinutes();
        $schedule->command('notificaciones:enviar')->everyMinute();
        
        $schedule->command('quizzes:finish-summary-overdue')->everyMinute();

        // Meetings
        $schedule->command('meeting:update-status')->hourly();
        $schedule->command('meeting:update-attendance-detail')->everyFiveMinutes();
        $schedule->command('meeting:verify-finish-status')->everyTenMinutes();
        $schedule->command('meeting:update-url-start')->everyTenMinutes();

        // Accounts
        $schedule->command('account:update-tokens')->monthly();

        $schedule->command('errores:eliminar-antiguos')->dailyAt('00:00');
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
