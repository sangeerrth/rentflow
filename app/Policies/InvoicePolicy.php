<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        $lease = $invoice->lease;

        return $lease->tenant_id === $user->id
            || $lease->unit->property->landlord_id === $user->id;
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return $invoice->lease->tenant_id === $user->id;
    }
}