<?php

namespace App\Policies\Sipancong;

use App\Models\User;
use App\Models\Sipancong\StatusPengajuan;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPengajuanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('view_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('update_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('delete_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('force_delete_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('restore_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, StatusPengajuan $statusPengajuan): bool
    {
        return $user->can('replicate_sipancong::status::pengajuan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sipancong::status::pengajuan');
    }
}
