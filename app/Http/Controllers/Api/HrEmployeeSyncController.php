<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HrEmployeeSyncController extends Controller
{
    private const STATUS_ALLOWED = ['Active', 'Inactive', 'Resigned', 'Terminated'];

    public function sync(Request $request): JsonResponse
    {
        $requestId = $request->header('X-REQUEST-ID') ?? $request->header('X-Request-Id');
        $source = $request->input('source_system', 'unknown');

        $validator = Validator::make($request->all(), [
            'source_system' => 'required|string|max:50',
            'sent_at' => 'required|date',
            'employees' => 'required|array|min:1',
            'employees.*.external_id' => 'required|string|max:100',
            'employees.*.employee_id' => 'nullable|string|max:100',
            'employees.*.email' => 'nullable|email|max:255',
            'employees.*.department' => 'nullable|string|max:100',
            'employees.*.position' => 'nullable|string|max:100',
            'employees.*.manager_name' => 'nullable|string|max:255',
            'employees.*.hire_date' => 'nullable|date_format:Y-m-d',
            'employees.*.employment_type' => 'nullable|string|max:50',
            'employees.*.work_location' => 'nullable|string|max:255',
            'employees.*.emergency_contact_name' => 'nullable|string|max:255',
            'employees.*.emergency_contact_phone' => 'nullable|string|max:50',
            'employees.*.address' => 'nullable|string|max:500',
            'employees.*.status' => 'nullable|string|in:' . implode(',', self::STATUS_ALLOWED),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload',
                'errors' => $validator->errors(),
            ], 400);
        }

        $employees = $request->input('employees', []);
        $received = count($employees);
        $inserted = 0;
        $updated = 0;
        $unlinked = 0;
        $skipped = 0;
        $errors = [];

        foreach ($employees as $index => $payload) {
            $rowId = $payload['external_id'] ?? "row_{$index}";

            try {
                DB::beginTransaction();

                $employee = Employee::where('external_id', $payload['external_id'])->first();
                $isNew = !$employee;

                $allowed = $this->buildAllowedUpdate($payload);

                if ($isNew) {
                    $employee = new Employee();
                    $employee->external_id = $payload['external_id'];
                }

                foreach ($allowed as $field => $value) {
                    $employee->{$field} = $value;
                }

                $employee->save();

                if ($isNew) {
                    $inserted++;
                } else {
                    $updated++;
                }

                // Link user_id by email if present and user_id is null
                $email = $payload['email'] ?? null;
                if ($email) {
                    $user = User::where('email', $email)->first();
                    if ($user) {
                        if (empty($employee->user_id)) {
                            $employee->user_id = $user->id;
                            $employee->save();
                        }
                    } else {
                        $unlinked++;
                    }
                } else {
                    $unlinked++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
                $errors[] = [
                    'external_id' => $rowId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('[HR Employee Sync] completed', [
            'source_system' => $source,
            'request_id' => $requestId,
            'received' => $received,
            'inserted' => $inserted,
            'updated' => $updated,
            'unlinked' => $unlinked,
            'skipped' => $skipped,
            'errors' => count($errors),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employees synced successfully',
            'received' => $received,
            'inserted' => $inserted,
            'updated' => $updated,
            'unlinked' => $unlinked,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    private function buildAllowedUpdate(array $payload): array
    {
        $allowed = [
            'employee_id',
            'department',
            'position',
            'manager_name',
            'hire_date',
            'employment_type',
            'work_location',
            'emergency_contact_name',
            'emergency_contact_phone',
            'address',
            'status',
        ];

        $data = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $value = $payload[$field];
                if ($field === 'hire_date' && $value) {
                    $value = Carbon::parse($value)->format('Y-m-d');
                }
                $data[$field] = $value;
            }
        }

        return $data;
    }
}
