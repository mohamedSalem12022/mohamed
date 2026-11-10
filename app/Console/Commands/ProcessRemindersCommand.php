<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Models\InAppNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessRemindersCommand extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process and deliver due reminders into in-app notifications';

    public function handle(): int
    {
        $now = Carbon::now();
        $this->info("Processing due reminders at {$now->toDateTimeString()}...");

        // Fetch pending reminders that are due
        $dueReminders = Reminder::with(['user', 'task', 'calendarEvent'])
            ->where('status', 'pending')
            ->where('remind_at', '<=', $now)
            ->get();

        $processedCount = 0;

        foreach ($dueReminders as $reminder) {
            // Check if user still exists
            if (!$reminder->user) {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            // Check if task is already completed (for task reminders)
            if ($reminder->task && $reminder->task->isCompleted()) {
                $reminder->update([
                    'status' => 'cancelled',
                    'sent_at' => $now,
                ]);
                continue;
            }

            // Determine notification details
            $title = $reminder->title;
            $body = 'حان موعد التذكير المحدد.';
            $link = route('dashboard');

            if ($reminder->task) {
                $body = "تذكير بمهمة: «{$reminder->task->title}»";
                $link = route('tasks.index');
            } elseif ($reminder->calendarEvent) {
                $body = "تذكير بحدث في التقويم: «{$reminder->calendarEvent->title}»";
                $link = route('calendar.index');
            }

            // Deduplication: check if already notified in the last 10 minutes for this reminder
            $exists = InAppNotification::where('reminder_id', $reminder->id)
                ->where('created_at', '>=', $now->copy()->subMinutes(10))
                ->exists();

            if (!$exists) {
                InAppNotification::create([
                    'user_id' => $reminder->user_id,
                    'reminder_id' => $reminder->id,
                    'title' => $title,
                    'body' => $body,
                    'link' => $link,
                    'type' => 'reminder',
                    'is_read' => false,
                ]);
            }

            // Mark reminder as sent
            $reminder->update([
                'status' => 'sent',
                'sent_at' => $now,
            ]);

            $processedCount++;
        }

        $this->info("Processed {$processedCount} reminders successfully.");
        return Command::SUCCESS;
    }
}
