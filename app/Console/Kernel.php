<?php

namespace App\Console;

use Illuminate\Support\Facades\Mail;
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
        \App\Console\Commands\SetupApplication::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $log = storage_path('logs/laravel.log');
            if (file_exists($log)) {
                $text = 'Daily laravel.log report';
            } else {
                $text = 'Everything works fine';
            }
            Mail::raw($text, function ($message) use ($log) {
                $message
                    ->to(config('app.report_to'))
                    ->subject('Daily report from ' . config('app.name'));

                if (file_exists($log)) {
                    $message->attach($log);
                    unlink($log);
                }
            });
        })->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
