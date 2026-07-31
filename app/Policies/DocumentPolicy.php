<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Detainee;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->is_active && $user->canAccessFacility($document->detainee->facility_id);
    }

    public function create(User $user, Detainee $detainee): bool
    {
        return $user->canManageOperations() && $user->canAccessFacility($detainee->facility_id);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->canManageOperations() && $user->canAccessFacility($document->detainee->facility_id);
    }
}
