<?php

namespace App\Policies;

use App\Models\HasilTesTulis;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HasilTesTulisPolicy
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
    public function view(User $user, HasilTesTulis $hasilTesTulis): bool
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
    public function update(User $user, HasilTesTulis $hasilTesTulis): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HasilTesTulis $hasilTesTulis): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, HasilTesTulis $hasilTesTulis): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, HasilTesTulis $hasilTesTulis): bool
    {
        return false;
    }
}
