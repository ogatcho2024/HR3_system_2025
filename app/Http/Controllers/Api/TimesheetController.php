<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TimesheetController extends Controller
{
    /**
     * Retrieve employee timesheets with filtering and pagination.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'employee_id' => ['sometimes', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/i'],
            'user_id' => ['sometimes', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in(['draft', 'submitted', 'approved', 'rejected'])],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error' => 'VALIDATION_ERROR'
            ], 422);
        }

        $validated = $validator->validated();

        // Set default values
        $limit = $validated['limit'] ?? 100;
        $offset = $validated['offset'] ?? 0;

        try {
            // Build query
            $query = Timesheet::query()
                ->with([
                    'user:id,name,email',
                    'approvedBy:id,name'
                ])
                ->join('employees', 'timesheets.employee_id', '=', 'employees.id')
                ->select(
                    'timesheets.*',
                    'employees.user_id',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.email',
                    'employees.department'
                );

            // Apply filters
            if (isset($validated['employee_id'])) {
                $query->where('timesheets.employee_id', $validated['employee_id']);
            }

            if (isset($validated['user_id'])) {
                $query->where('employees.user_id', $validated['user_id']);
            }

            if (isset($validated['start_date'])) {
                $query->where('timesheets.date', '>=', $validated['start_date']);
            }

            if (isset($validated['end_date'])) {
                $query->where('timesheets.date', '<=', $validated['end_date']);
            }

            if (isset($validated['status'])) {
                $query->where('timesheets.status', $validated['status']);
            }

            // Get total count before pagination
            $total = $query->count();

            // Apply pagination
            $timesheets = $query
                ->orderBy('timesheets.date', 'desc')
                ->orderBy('timesheets.created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            // Format the response
            $formattedTimesheets = $timesheets->map(function ($timesheet) {
                return [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->employee_id,
                    'user_id' => $timesheet->user_id,
                    'employee' => [
                        'first_name' => $timesheet->first_name,
                        'last_name' => $timesheet->last_name,
                        'full_name' => trim($timesheet->first_name . ' ' . $timesheet->last_name),
                        'email' => $timesheet->email,
                        'department' => $timesheet->department
                    ],
                    'date' => $timesheet->date,
                    'project_name' => $timesheet->project_name,
                    'task_description' => $timesheet->task_description,
                    'hours_worked' => round($timesheet->hours_worked / 60, 2), // Convert minutes to hours
                    'is_overtime' => (bool) $timesheet->is_overtime,
                    'status' => $timesheet->status,
                    'approved_by' => $timesheet->approved_by,
                    'approved_by_name' => $timesheet->approvedBy?->name,
                    'approved_at' => $timesheet->approved_at?->toISOString(),
                    'notes' => $timesheet->notes,
                    'created_at' => $timesheet->created_at->toISOString(),
                    'updated_at' => $timesheet->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedTimesheets,
                'meta' => [
                    'total' => $total,
                    'count' => $formattedTimesheets->count(),
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $formattedTimesheets->count()) < $total
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Timesheet API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve timesheets',
                'error' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Retrieve a specific timesheet by ID.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $timesheet = Timesheet::query()
                ->with([
                    'user:id,name,email',
                    'approvedBy:id,name'
                ])
                ->join('employees', 'timesheets.employee_id', '=', 'employees.id')
                ->select(
                    'timesheets.*',
                    'employees.user_id',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.email',
                    'employees.department'
                )
                ->where('timesheets.id', $id)
                ->first();

            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            $formattedTimesheet = [
                'id' => $timesheet->id,
                'employee_id' => $timesheet->employee_id,
                'user_id' => $timesheet->user_id,
                'employee' => [
                    'first_name' => $timesheet->first_name,
                    'last_name' => $timesheet->last_name,
                    'full_name' => trim($timesheet->first_name . ' ' . $timesheet->last_name),
                    'email' => $timesheet->email,
                    'department' => $timesheet->department
                ],
                'date' => $timesheet->date,
                'project_name' => $timesheet->project_name,
                'task_description' => $timesheet->task_description,
                'hours_worked' => round($timesheet->hours_worked / 60, 2),
                'is_overtime' => (bool) $timesheet->is_overtime,
                'status' => $timesheet->status,
                'approved_by' => $timesheet->approved_by,
                'approved_by_name' => $timesheet->approvedBy?->name,
                'approved_at' => $timesheet->approved_at?->toISOString(),
                'notes' => $timesheet->notes,
                'created_at' => $timesheet->created_at->toISOString(),
                'updated_at' => $timesheet->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedTimesheet
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Timesheet show error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve timesheet',
                'error' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
