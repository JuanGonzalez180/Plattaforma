<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TaskTenderClosed::class,
        Commands\TaskQuoteClosed::class,
        Commands\TaskDownloadImgProduct::class,
        Commands\TaskSendInvitationUnregisteredCompanies::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // cronJobs para cerrar licitaciones
        $schedule->command('task:tender_closed')->everyMinute();
        // cronJobs para cerrar cotizaciones
        $schedule->command('task:quote_closed')->everyMinute();
        // cronJobs para descargar las imagenes y archivos de los productor cargados por carga masiva
        $schedule->command('task:download_img_product')->everyFifteenMinutes();
        //cronJobs para enviar invitaciones a licitaciones a compañias no registradas a plattaforma
        $schedule->command('task:task_send_invitation_unregistered_companies')->everyFiveMinutes();
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
