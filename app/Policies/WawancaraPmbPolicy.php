<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WawancaraPmb;
use Illuminate\Auth\Access\Response;

class WawancaraPmbPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WawancaraPmb $wawancaraPmb): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WawancaraPmb $wawancaraPmb): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WawancaraPmb $wawancaraPmb): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WawancaraPmb $wawancaraPmb): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WawancaraPmb $wawancaraPmb): bool
    {
        return false;
    }
}
