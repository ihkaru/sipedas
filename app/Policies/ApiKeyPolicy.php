<?php

namespace App\Policies;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiKeyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApiKey $apiKey): bool
    {
        if ($user->hasRole(['super_admin', 'operator_umum'])) {
            return true;
        }

        return $apiKey->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'operator_umum']);
    }

    public function update(User $user, ApiKey $apiKey): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $apiKey->user_id === $user->id;
    }

    public function delete(User $user, ApiKey $apiKey): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $apiKey->user_id === $user->id;
    }
}
