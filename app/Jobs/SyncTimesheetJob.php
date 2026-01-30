<?php

namespace App\Jobs;

use App\Models\Timesheet;
use App\Services\TimesheetSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTimesheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The timesheet instance.
     *
     * @var Timesheet
     */
    public $timesheet;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param Timesheet $timesheet
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }

    /**
     * Execute the job.
     *
     * @param TimesheetSenderService $senderService
     * @return void
     */
    public function handle(TimesheetSenderService $senderService): void
    {
        Log::info('SyncTimesheetJob started', [
            'timesheet_id' => $this->timesheet->id,
            'attempt' => $this->attempts(),
        ]);

        $result = $senderService->sendToPayroll($this->timesheet);

        if (!$result['success']) {
            Log::warning('SyncTimesheetJob failed, will retry if attempts remain', [
                'timesheet_id' => $this->timesheet->id,
                'attempt' => $this->attempts(),
                'result' => $result,
            ]);

            // If this was not successful and we have attempts left, fail the job so it retries
            if ($this->attempts() < $this->tries) {
                throw new \Exception($result['message']);
            }
        } else {
            Log::info('SyncTimesheetJob completed successfully', [
                'timesheet_id' => $this->timesheet->id,
                'result' => $result,
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncTimesheetJob permanently failed', [
            'timesheet_id' => $this->timesheet->id,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Here you could:
        // - Send notification to admin
        // - Update timesheet with sync_failed flag
        // - Store in a failed_syncs table for manual review
    }
}
