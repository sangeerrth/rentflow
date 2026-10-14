<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy
{
    private function isLandlordOf(User $user, Lease $lease): bool
    {
        return $lease->unit->property->landlord_id === $user->id;
    }

    public function view(User $user, Lease $lease): bool
    {
        return $this->isLandlordOf($user, $lease)
            || $lease->tenant_id === $user->id;
    }

    public function terminate(User $user, Lease $lease): bool
    {
        return $this->isLandlordOf($user, $lease);
    }
}