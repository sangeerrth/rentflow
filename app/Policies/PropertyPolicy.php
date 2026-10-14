<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function view(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }

    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }
}