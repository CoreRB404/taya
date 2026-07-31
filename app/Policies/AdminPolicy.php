<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manageFacilities(User $user): bool
    {
        return $user->isAdmin();
    }

    public function managePenalties(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewAuditLogs(User $user): bool
    {
        return $user->isAdmin() || $user->isAuditor();
    }
}
