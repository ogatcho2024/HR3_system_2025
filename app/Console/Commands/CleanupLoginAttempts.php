<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAttempt;

class CleanupLoginAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login-attempts:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old login attempts records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up old login attempts...');
        
        $deleted = LoginAttempt::cleanup();
        
        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old login attempt records.");
        } else {
            $this->info('No old login attempts to clean up.');
        }
        
        return 0;
    }
}