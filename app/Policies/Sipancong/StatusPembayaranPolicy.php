<?php

namespace App\Policies\Sipancong;

use App\Models\User;
use App\Models\Sipancong\StatusPembayaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPembayaranPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('view_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('update_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('delete_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('force_delete_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('restore_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, StatusPembayaran $statusPembayaran): bool
    {
        return $user->can('replicate_sipancong::status::pembayaran');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sipancong::status::pembayaran');
    }
}
