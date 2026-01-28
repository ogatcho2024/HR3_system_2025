<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Illuminate\Support\Str;

class GenerateQrSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate-secrets 
                            {--force : Force regeneration of all QR secrets, even for employees who already have one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR secrets for employees who don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('Starting QR secret generation...');
        $this->newLine();

        // Get employees based on force flag
        if ($force) {
            $employees = Employee::all();
            $this->warn('Force flag enabled - regenerating secrets for ALL employees');
        } else {
            $employees = Employee::whereNull('qr_secret')
                ->orWhere('qr_secret', '')
                ->get();
        }

        if ($employees->isEmpty()) {
            $this->info('✓ All employees already have QR secrets!');
            return Command::SUCCESS;
        }

        $this->info("Found {$employees->count()} employee(s) without QR secrets");
        $this->newLine();

        $bar = $this->output->createProgressBar($employees->count());
        $bar->start();

        $generated = 0;
        $failed = 0;

        foreach ($employees as $employee) {
            try {
                // Generate a secure random secret and hash it
                $secret = hash('sha256', Str::random(32));
                $employee->qr_secret = $secret;
                $employee->save();

                $generated++;
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nFailed to generate secret for Employee ID {$employee->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("QR Secret Generation Complete!");
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Successfully Generated', $generated],
                ['Failed', $failed],
                ['Total Processed', $employees->count()],
            ]
        );

        if ($generated > 0) {
            $this->info("✓ {$generated} QR secret(s) generated successfully");
        }

        if ($failed > 0) {
            $this->error("✗ {$failed} secret(s) failed to generate");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
