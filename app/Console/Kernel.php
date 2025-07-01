<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * 
     * For Windows 11 users, you can set up a scheduled task using PowerShell:
     * 
     * 1. Create a PowerShell script named ProcessEmails.ps1 with the following content:
     *    ```
     *    cd C:\path\to\your\project
     *    php artisan emails:process --all
     *    ```
     * 
     * 2. Open Task Scheduler (taskschd.msc)
     * 3. Create a new Basic Task:
     *    - Name it "Process Email Inboxes"
     *    - Set trigger to run every 5 minutes
     *    - Action: Start a program
     *    - Program/script: powershell.exe
     *    - Arguments: -ExecutionPolicy Bypass -File "C:\path\to\ProcessEmails.ps1"
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process all active email inboxes every 5 minutes
        $schedule->command('emails:process --all')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/email-processing.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 