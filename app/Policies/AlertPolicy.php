<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Alert $alert): bool
    {
        return $user->is_active && $user->canAccessFacility($alert->detainee->facility_id);
    }

    public function assign(User $user, Alert $alert): bool
    {
        return ($user->canManageOperations() || $user->isLawyer()) && $this->view($user, $alert);
    }

    public function resolve(User $user, Alert $alert): bool
    {
        return ($user->canManageOperations() || $user->isLawyer()) && $this->view($user, $alert);
    }

    public function override(User $user, Alert $alert): bool
    {
        return $user->isAdmin();
    }
}
