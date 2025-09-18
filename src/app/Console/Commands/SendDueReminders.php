<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Reminder;
use App\Mail\ReminderMail;

class SendDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-due-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails for due reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for due reminders...');
        
        $currentTimestamp = time();
        $dueReminders = Reminder::with(['creator'])
                            ->where('remind_at', '<=', $currentTimestamp)
                            ->where('status', 'pending')
                            ->get();

        $this->info("Found " . $dueReminders->count() . " reminders");
        $count = 0;
        foreach ($dueReminders as $reminder) {
            try {
                if (!$reminder->creator || !$reminder->creator->email) {
                    $this->error("No valid email for reminder {$reminder->email}");
                    $reminder->update(['status' => 'failed']);
                    continue;
                }

                Mail::to($reminder->creator->email)->send(new ReminderMail($reminder));
                $reminder->update(['status' => 'sent']);
                
                $count++;
                $this->info("Sent reminder: {$reminder->title}");
                
            } catch (\Exception $e) {
                $this->error("Failed to send reminder {$reminder->id}: " . $e->getMessage());
                $reminder->update(['status' => 'failed']);
            }
        }

        $this->info("Processed {$count} reminders");
    }
}
