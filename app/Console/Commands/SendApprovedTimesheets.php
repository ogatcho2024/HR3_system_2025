<?php

namespace App\Console\Commands;

use App\Models\Timesheet;
use App\Services\TimesheetSenderService;
use Illuminate\Console\Command;

class SendApprovedTimesheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timesheets:send-approved {--limit=30 : Number of timesheets to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send approved timesheets to HR4 payroll system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TimesheetSenderService $senderService)
    {
        $limit = (int) $this->option('limit');

        $this->info("Fetching {$limit} approved timesheets...");

        $timesheets = Timesheet::where('status', 'approved')
            ->take($limit)
            ->get();

        if ($timesheets->isEmpty()) {
            $this->warn('No approved timesheets found.');
            return 0;
        }

        $this->info("Found {$timesheets->count()} approved timesheets. Sending to HR4...");

        $result = $senderService->sendBatch($timesheets);

        $this->newLine();
        $this->info("=== RESULTS ===");
        $this->info("Total: {$timesheets->count()}");
        $this->info("Successful: {$result['success_count']}");
        $this->error("Failed: {$result['failed_count']}");

        if ($result['failed_count'] > 0) {
            $this->newLine();
            $this->error("Failed timesheets:");
            foreach ($result['results'] as $res) {
                if (!$res['success']) {
                    $this->error("  - Timesheet ID {$res['timesheet_id']}: {$res['message']}");
                }
            }
        }

        return $result['failed_count'] > 0 ? 1 : 0;
    }
}
