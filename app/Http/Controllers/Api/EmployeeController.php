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
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric', 
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

            // Get user from token (using our custom authentication)
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $tokenValue = substr($token, 7);
                $apiToken = \App\Models\ApiToken::where('token', $tokenValue)->with('user')->first();
                if (!$apiToken || $apiToken->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
                $user = $apiToken->user;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token required'
                ], 401);
            }

            $now = $request->timestamp ? Carbon::parse($request->timestamp) : Carbon::now();

            // Check if already clocked in today
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $now->toDateString())
                ->whereNotNull('clock_in_time')
                ->whereNull('clock_out_time')
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
                'clock_in_time' => $now->format('H:i:s'),
                'status' => 'present',
                'created_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'data' => [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'clock_in_time' => $attendance->clock_in_time,
                    'status' => $attendance->status,
                    'timestamp' => $now->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Clock in failed: ' . $e->getMessage()
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
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
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

            // Get user from token (using our custom authentication)
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $tokenValue = substr($token, 7);
                $apiToken = \App\Models\ApiToken::where('token', $tokenValue)->with('user')->first();
                if (!$apiToken || $apiToken->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
                $user = $apiToken->user;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token required'
                ], 401);
            }

            $now = $request->timestamp ? Carbon::parse($request->timestamp) : Carbon::now();

            // Find today's attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $now->toDateString())
                ->whereNotNull('clock_in_time')
                ->whereNull('clock_out_time')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active clock-in found for today'
                ], 400);
            }

            // Calculate hours worked using the model's method
            $attendance->clock_out_time = $now->format('H:i:s');
            $hoursWorked = $attendance->calculateHours();

            // Update attendance record
            $attendance->update([
                'clock_out_time' => $now->format('H:i:s'),
                'hours_worked' => $hoursWorked
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully',
                'data' => [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'clock_in_time' => $attendance->clock_in_time,
                    'clock_out_time' => $attendance->clock_out_time,
                    'hours_worked' => $hoursWorked,
                    'status' => $attendance->status,
                    'timestamp' => $now->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Clock out failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records
     */
    public function getAttendance(Request $request)
    {
        try {
            // Get user from token (using our custom authentication)
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $tokenValue = substr($token, 7);
                $apiToken = \App\Models\ApiToken::where('token', $tokenValue)->with('user')->first();
                if (!$apiToken || $apiToken->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
                $user = $apiToken->user;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token required'
                ], 401);
            }

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
                        'clock_in_time' => $attendance->clock_in_time,
                        'clock_out_time' => $attendance->clock_out_time,
                        'hours_worked' => $attendance->hours_worked,
                        'overtime_hours' => $attendance->overtime_hours,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get attendance failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current clock status
     */
    public function getClockStatus(Request $request)
    {
        try {
            // Get user from token (using our custom authentication)
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $tokenValue = substr($token, 7);
                $apiToken = \App\Models\ApiToken::where('token', $tokenValue)->with('user')->first();
                if (!$apiToken || $apiToken->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
                $user = $apiToken->user;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token required'
                ], 401);
            }

            $today = Carbon::now()->toDateString();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            $isCurrentlyClockedIn = $attendance && $attendance->clock_in_time && !$attendance->clock_out_time;

            return response()->json([
                'success' => true,
                'data' => [
                    'is_clocked_in' => $isCurrentlyClockedIn,
                    'last_clock_time' => $attendance ? ($attendance->clock_out_time ?: $attendance->clock_in_time) : null,
                    'today_attendance' => $attendance ? [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'clock_in_time' => $attendance->clock_in_time,
                        'clock_out_time' => $attendance->clock_out_time,
                        'hours_worked' => $attendance->hours_worked,
                        'status' => $attendance->status
                    ] : null
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get status failed: ' . $e->getMessage()
            ], 500);
        }
    }
}