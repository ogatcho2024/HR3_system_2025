<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep read notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old read notifications to keep the database clean';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up read notifications older than {$days} days...");
        
        $deletedCount = $notificationService->cleanupOldNotifications($days);
        
        $this->info("Successfully deleted {$deletedCount} old notifications.");
        
        return Command::SUCCESS;
    }
}
