<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KegiatanManmit;
use Illuminate\Auth\Access\HandlesAuthorization;

class KegiatanManmitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_kegiatan::manmit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('view_kegiatan::manmit');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_kegiatan::manmit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('update_kegiatan::manmit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('delete_kegiatan::manmit');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_kegiatan::manmit');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('force_delete_kegiatan::manmit');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_kegiatan::manmit');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('restore_kegiatan::manmit');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_kegiatan::manmit');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, KegiatanManmit $kegiatanManmit): bool
    {
        return $user->can('replicate_kegiatan::manmit');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_kegiatan::manmit');
    }
}
