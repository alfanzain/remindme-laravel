<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReminderDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run reminder checker continuously';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting reminder daemon...');
        
        while (true) {
            $this->info('Running reminder check at ' . now());
            
            try {
                Artisan::call('app:send-due-reminders');
                $this->info('Reminder check completed');
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }
            
            $this->info('Waiting 60 seconds...');
            sleep(60);
        }
    }
}
