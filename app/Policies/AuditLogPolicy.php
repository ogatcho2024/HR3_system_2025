<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine if the user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        // Only Super Admin and Admin can view audit logs
        return in_array($user->account_type, [
            User::ACCOUNT_TYPE_SUPER_ADMIN,
            User::ACCOUNT_TYPE_ADMIN
        ]);
    }

    /**
     * Determine if the user can view a specific audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        // Only Super Admin can view audit log details
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    /**
     * Determine if the user can export audit logs.
     */
    public function export(User $user): bool
    {
        // Only Super Admin can export audit logs
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    /**
     * Determine if the user can delete audit logs.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        // Only Super Admin can delete audit logs
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    /**
     * Determine if the user can delete any audit logs.
     */
    public function deleteAny(User $user): bool
    {
        // Only Super Admin can delete audit logs
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }
}
