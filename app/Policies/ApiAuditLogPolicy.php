<?php

namespace App\Policies;

use App\Models\ApiAuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiAuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'operator_umum']);
    }

    public function view(User $user, ApiAuditLog $log): bool
    {
        return $user->hasRole(['super_admin', 'operator_umum']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ApiAuditLog $log): bool
    {
        return false;
    }

    public function delete(User $user, ApiAuditLog $log): bool
    {
        return false;
    }
}
