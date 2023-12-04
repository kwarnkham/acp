<?php

namespace App\Console;

use App\Jobs\CleanUpPictures;
use App\Jobs\CleanUpRounds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('backup:clean')->daily()->at('19:00');
        $schedule->command('backup:run')->daily()->at('19:30');
        $schedule->job(new CleanUpRounds)->dailyAt('20:00'); //02:30
        $schedule->job(new CleanUpPictures)->dailyAt('20:30'); //03:00
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
