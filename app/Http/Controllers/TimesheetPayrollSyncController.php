<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Services\TimesheetSenderService;
use App\Jobs\SyncTimesheetJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TimesheetPayrollSyncController extends Controller
{
    /**
     * The timesheet sender service instance.
     *
     * @var TimesheetSenderService
     */
    protected $senderService;

    /**
     * Create a new controller instance.
     *
     * @param TimesheetSenderService $senderService
     */
    public function __construct(TimesheetSenderService $senderService)
    {
        $this->middleware('auth');
        $this->senderService = $senderService;
    }

    /**
     * Send a single timesheet to payroll (synchronous).
     *
     * @param Request $request
     * @param int $timesheetId
     * @return JsonResponse
     */
    public function sendTimesheet(Request $request, int $timesheetId): JsonResponse
    {
        try {
            $timesheet = Timesheet::findOrFail($timesheetId);

            Log::info('Manual timesheet sync triggered', [
                'timesheet_id' => $timesheetId,
                'user_id' => auth()->id(),
            ]);

            $result = $this->senderService->sendToPayroll($timesheet);

            return response()->json($result, $result['status_code'] ?? 200);

        } catch (\Exception $e) {
            Log::error('Error in sendTimesheet controller', [
                'timesheet_id' => $timesheetId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Queue a timesheet for async sending to payroll.
     *
     * @param Request $request
     * @param int $timesheetId
     * @return JsonResponse
     */
    public function queueTimesheet(Request $request, int $timesheetId): JsonResponse
    {
        try {
            $timesheet = Timesheet::findOrFail($timesheetId);

            if ($timesheet->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved timesheets can be queued for payroll sync.',
                    'timesheet_id' => $timesheetId,
                ], 400);
            }

            // Dispatch job to queue
            SyncTimesheetJob::dispatch($timesheet);

            Log::info('Timesheet queued for payroll sync', [
                'timesheet_id' => $timesheetId,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timesheet queued for payroll sync.',
                'timesheet_id' => $timesheetId,
            ], 202);

        } catch (\Exception $e) {
            Log::error('Error in queueTimesheet controller', [
                'timesheet_id' => $timesheetId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send multiple approved timesheets to payroll.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendBatch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'timesheet_ids' => 'required|array|min:1',
                'timesheet_ids.*' => 'required|integer|exists:timesheets,id',
            ]);

            $timesheets = Timesheet::whereIn('id', $validated['timesheet_ids'])
                ->where('status', 'approved')
                ->get();

            if ($timesheets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved timesheets found with provided IDs.',
                ], 404);
            }

            Log::info('Batch timesheet sync triggered', [
                'count' => $timesheets->count(),
                'user_id' => auth()->id(),
            ]);

            $result = $this->senderService->sendBatch($timesheets);

            return response()->json([
                'success' => true,
                'message' => 'Batch sync completed.',
                'summary' => [
                    'total' => $timesheets->count(),
                    'successful' => $result['success_count'],
                    'failed' => $result['failed_count'],
                ],
                'results' => $result['results'],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in sendBatch controller', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Queue multiple timesheets for async sending.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function queueBatch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'timesheet_ids' => 'required|array|min:1',
                'timesheet_ids.*' => 'required|integer|exists:timesheets,id',
            ]);

            $timesheets = Timesheet::whereIn('id', $validated['timesheet_ids'])
                ->where('status', 'approved')
                ->get();

            if ($timesheets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved timesheets found with provided IDs.',
                ], 404);
            }

            // Dispatch jobs for each timesheet
            foreach ($timesheets as $timesheet) {
                SyncTimesheetJob::dispatch($timesheet);
            }

            Log::info('Batch timesheets queued for payroll sync', [
                'count' => $timesheets->count(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timesheets queued for payroll sync.',
                'count' => $timesheets->count(),
            ], 202);

        } catch (\Exception $e) {
            Log::error('Error in queueBatch controller', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-sync all approved timesheets from a specific date range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autoSyncDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'async' => 'boolean',
            ]);

            $timesheets = Timesheet::where('status', 'approved')
                ->whereBetween('work_date', [$validated['start_date'], $validated['end_date']])
                ->get();

            if ($timesheets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved timesheets found in date range.',
                ], 404);
            }

            $async = $validated['async'] ?? true;

            if ($async) {
                // Queue jobs
                foreach ($timesheets as $timesheet) {
                    SyncTimesheetJob::dispatch($timesheet);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Timesheets queued for payroll sync.',
                    'count' => $timesheets->count(),
                ], 202);
            } else {
                // Synchronous batch send
                $result = $this->senderService->sendBatch($timesheets);

                return response()->json([
                    'success' => true,
                    'message' => 'Batch sync completed.',
                    'summary' => [
                        'total' => $timesheets->count(),
                        'successful' => $result['success_count'],
                        'failed' => $result['failed_count'],
                    ],
                    'results' => $result['results'],
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('Error in autoSyncDateRange controller', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
