<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\QrAttendanceLog;
use App\Services\QrAttendanceService;
use Carbon\Carbon;

class QrAttendanceController extends Controller
{
    protected $qrAttendanceService;

    public function __construct(QrAttendanceService $qrAttendanceService)
    {
        $this->middleware('auth');
        $this->qrAttendanceService = $qrAttendanceService;
    }

    /**
     * Show employee's daily QR code page.
     */
    public function showEmployeeQr(): View
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(404, 'Employee record not found. Please contact HR.');
        }

        // Generate today's QR token
        $dailyToken = $employee->generateDailyQrToken();
        $currentDate = date('Y-m-d');

        // Get today's attendance stats
        $stats = $this->qrAttendanceService->getAttendanceStats($employee->id);

        // Prepare QR payload
        $qrPayload = json_encode([
            'token' => $dailyToken,
            'emp_id' => $employee->id,
            'date' => $currentDate,
        ]);

        return view('employee.qr-today', compact('employee', 'user', 'qrPayload', 'currentDate', 'stats'));
    }

    /**
     * Show QR scanner page (Admin/Staff only).
     */
    public function showScanner(): View
    {
        return view('attendance.scanner');
    }

    /**
     * Process QR code scan and log attendance.
     */
    public function processScan(Request $request): JsonResponse
    {
        // Log incoming request for debugging
        \Log::info('QR Scan Request', [
            'data' => $request->all(),
            'ip' => $request->ip(),
        ]);

        // Validate request
        $validated = $request->validate([
            'token' => 'required|string',
            'emp_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        try {
            // Verify date is today
            $scannedDate = Carbon::parse($validated['date'])->format('Y-m-d');
            $today = Carbon::today()->format('Y-m-d');

            if ($scannedDate !== $today) {
                \Log::warning('QR code date mismatch', [
                    'scanned_date' => $scannedDate,
                    'today' => $today,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR code is not valid for today. Please generate a new QR code.',
                ], 422);
            }

            // Find employee
            $employee = Employee::find($validated['emp_id']);
            if (!$employee) {
                \Log::error('Employee not found', [
                    'emp_id' => $validated['emp_id'],
                    'total_employees' => Employee::count(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Employee not found with ID: {$validated['emp_id']}. Please verify your employee record exists in the system.",
                ], 404);
            }
            
            \Log::info('Employee found', [
                'emp_id' => $employee->id,
                'name' => $employee->user->name ?? 'N/A',
            ]);

            // Verify token
            if (!$employee->verifyDailyToken($validated['token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired QR code.',
                ], 422);
            }

            // Check if employee can log attendance (cooldown + max logs)
            $canLog = $this->qrAttendanceService->canLogAttendance($employee->id);
            if (!$canLog['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $canLog['reason'],
                ], 429);
            }

            // Determine log type
            $logType = $this->qrAttendanceService->getNextLogType($employee->id);
            if (!$logType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine log type. Maximum entries may have been reached.',
                ], 422);
            }

            // Use database transaction
            DB::beginTransaction();
            try {
                // Create QR attendance log
                $qrLog = QrAttendanceLog::create([
                    'employee_id' => $employee->id,
                    'log_type' => $logType,
                    'scanned_at' => Carbon::now(),
                    'daily_token' => $validated['token'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Update or create attendance record
                $attendance = Attendance::firstOrNew([
                    'user_id' => $employee->user_id,
                    'date' => $today,
                ]);

                if ($logType === 'IN') {
                    $attendance->clock_in_time = Carbon::now()->format('H:i:s');
                    $attendance->status = 'present';
                    $attendance->created_by = Auth::id();
                } elseif ($logType === 'OUT') {
                    $attendance->clock_out_time = Carbon::now()->format('H:i:s');
                }

                $attendance->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'type' => $logType,
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->user->name . ' ' . $employee->user->lastname,
                        'employee_id' => $employee->employee_id,
                    ],
                    'time' => Carbon::now()->format('H:i:s'),
                    'message' => "Successfully logged {$logType} at " . Carbon::now()->format('g:i A'),
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the scan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
