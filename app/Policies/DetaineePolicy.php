<?php

namespace App\Policies;

use App\Models\Detainee;
use App\Models\User;

class DetaineePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Detainee $detainee): bool
    {
        return $user->is_active && $user->canAccessFacility($detainee->facility_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageOperations();
    }

    public function update(User $user, Detainee $detainee): bool
    {
        return $user->canManageOperations() && $user->canAccessFacility($detainee->facility_id);
    }

    public function delete(User $user, Detainee $detainee): bool
    {
        return $this->update($user, $detainee);
    }

    public function release(User $user, Detainee $detainee): bool
    {
        return $this->update($user, $detainee);
    }
}
