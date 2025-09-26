<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Clock in
     */
    public function clockIn(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'nullable|string',
                'timestamp' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $now = $request->timestamp ? Carbon::parse($request->timestamp) : Carbon::now();

            // Check if already clocked in today
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $now->toDateString())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already clocked in today'
                ], 400);
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $now->toDateString(),
                'clock_in' => $now->toTimeString(),
                'clock_in_latitude' => $request->latitude,
                'clock_in_longitude' => $request->longitude,
                'clock_in_address' => $request->address,
                'status' => 'present'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'data' => [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'clock_in' => $attendance->clock_in,
                    'location' => $request->address,
                    'timestamp' => $now->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clock out
     */
    public function clockOut(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'nullable|string',
                'timestamp' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $now = $request->timestamp ? Carbon::parse($request->timestamp) : Carbon::now();

            // Find today's attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $now->toDateString())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active clock-in found for today'
                ], 400);
            }

            // Calculate hours worked
            $clockIn = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
            $clockOut = $now;
            $hoursWorked = $clockIn->diffInHours($clockOut, true);

            // Update attendance record
            $attendance->update([
                'clock_out' => $now->toTimeString(),
                'clock_out_latitude' => $request->latitude,
                'clock_out_longitude' => $request->longitude,
                'clock_out_address' => $request->address,
                'hours_worked' => $hoursWorked
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully',
                'data' => [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'hours_worked' => $hoursWorked,
                    'location' => $request->address,
                    'timestamp' => $now->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records
     */
    public function getAttendance(Request $request)
    {
        try {
            $user = $request->user();
            $period = $request->get('period', 'monthly');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Attendance::where('user_id', $user->id);

            // Apply date filters based on period or custom dates
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                switch ($period) {
                    case 'weekly':
                        $query->where('date', '>=', Carbon::now()->startOfWeek());
                        break;
                    case 'yearly':
                        $query->where('date', '>=', Carbon::now()->startOfYear());
                        break;
                    case 'monthly':
                    default:
                        $query->where('date', '>=', Carbon::now()->startOfMonth());
                        break;
                }
            }

            $attendances = $query->orderBy('date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'hours_worked' => $attendance->hours_worked,
                        'status' => $attendance->status,
                        'clock_in_address' => $attendance->clock_in_address,
                        'clock_out_address' => $attendance->clock_out_address,
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current clock status
     */
    public function getClockStatus(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::now()->toDateString();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            $isCurrentlyClockedIn = $attendance && $attendance->clock_in && !$attendance->clock_out;

            return response()->json([
                'success' => true,
                'data' => [
                    'is_clocked_in' => $isCurrentlyClockedIn,
                    'last_clock_time' => $attendance ? ($attendance->clock_out ?: $attendance->clock_in) : null,
                    'today_attendance' => $attendance ? [
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'hours_worked' => $attendance->hours_worked,
                    ] : null
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}