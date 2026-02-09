<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use Illuminate\Validation\ValidationException;

class LeaveBalanceService
{
    public function ensureBalance(int $userId, string $leaveType, int $year): LeaveBalance
    {
        $policy = LeavePolicy::active()->forLeaveType($leaveType)->first();
        $entitlement = $policy ? (float) $policy->annual_entitlement : 0.0;

        $balance = LeaveBalance::firstOrCreate(
            [
                'user_id' => $userId,
                'leave_type' => $leaveType,
                'year' => $year,
            ],
            [
                'total_entitled' => $entitlement,
                'used' => 0,
                'pending' => 0,
                'carried_forward' => 0,
                'available' => $entitlement,
            ]
        );

        // If policy exists and balance is empty, initialize entitlement/available.
        if ($policy && (float) $balance->total_entitled === 0.0) {
            $balance->total_entitled = $entitlement;
            $this->recalculate($balance);
        }

        return $balance;
    }

    public function assertSufficient(LeaveBalance $balance, float $days): void
    {
        if ($days <= 0) {
            return;
        }
        if ($balance->available < $days) {
            throw ValidationException::withMessages([
                'leave_type' => 'Insufficient leave balance for this request.',
            ]);
        }
    }

    public function applyPending(LeaveBalance $balance, float $days): void
    {
        if ($days <= 0) {
            return;
        }
        $balance->pending += $days;
        $this->recalculate($balance);
    }

    public function applyApproval(LeaveBalance $balance, float $days): void
    {
        if ($days <= 0) {
            return;
        }
        $balance->pending = max(0, $balance->pending - $days);
        $balance->used += $days;
        $this->recalculate($balance);
    }

    public function removePending(LeaveBalance $balance, float $days): void
    {
        if ($days <= 0) {
            return;
        }
        $balance->pending = max(0, $balance->pending - $days);
        $this->recalculate($balance);
    }

    public function recalculate(LeaveBalance $balance): void
    {
        $balance->available = $balance->total_entitled + $balance->carried_forward - $balance->used - $balance->pending;
        $balance->save();
    }
}
