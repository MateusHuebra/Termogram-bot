<?php

namespace App\Console;

use App\Console\Scheduled\NotificateSubscribedUsers;
use App\Console\Scheduled\resetAndDistributeScore;
use App\Console\Scheduled\SendGamesPlayedToday;
use App\Console\Scheduled\DeleteOldLinesFromUpdatesTable;
use App\Console\Scheduled\reserAnd;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->call(new ScheduleTest)->everyMinute();
        $schedule->call(new resetAndDistributeScore)->twiceMonthly(1, 16, '00:00');
        $schedule->call(new NotificateSubscribedUsers)->hourly();
        $schedule->call(new SendGamesPlayedToday)->daily();
        $schedule->call(new DeleteOldLinesFromUpdatesTable)->daily();
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
