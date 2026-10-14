<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        return Lease::with(['unit.property:id,name', 'tenant:id,name,email'])
            ->whereHas('unit.property', fn ($q) => $q->where('landlord_id', $request->user()->id))
            ->latest()
            ->get();
    }

    public function myLeases(Request $request)
    {
        return $request->user()
            ->leases()
            ->with('unit.property:id,name,address_line,city')
            ->latest()
            ->get();
    }

    public function store(Request $request, Unit $unit)
    {
        Gate::authorize('update', $unit->property);

        $data = $request->validate([
            'tenant_email'     => 'required|email',
            'rent_amount'      => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'rent_due_day'     => 'nullable|integer|min:1|max:28',
            'grace_days'       => 'nullable|integer|min:0|max:30',
            'late_fee_amount'  => 'nullable|numeric|min:0',
        ]);

        $tenant = User::where('email', $data['tenant_email'])
            ->where('role', 'tenant')
            ->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'tenant_email' => ['No tenant account exists with this email.'],
            ]);
        }

        if ($unit->leases()->where('status', 'active')->exists()) {
            throw ValidationException::withMessages([
                'unit' => ['This unit already has an active lease.'],
            ]);
        }

        $lease = DB::transaction(function () use ($unit, $tenant, $data) {
            $lease = $unit->leases()->create([
                'tenant_id'        => $tenant->id,
                'rent_amount'      => $data['rent_amount'] ?? $unit->rent_amount,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'start_date'       => $data['start_date'],
                'end_date'         => $data['end_date'],
                'rent_due_day'     => $data['rent_due_day'] ?? 1,
                'grace_days'       => $data['grace_days'] ?? 5,
                'late_fee_amount'  => $data['late_fee_amount'] ?? 0,
                'status'           => 'active',
            ]);

            $unit->update(['status' => 'occupied']);

            return $lease;
        });

        return response()->json($lease->load('tenant:id,name,email'), 201);
    }

    public function show(Lease $lease)
    {
        Gate::authorize('view', $lease);

        return $lease->load(['unit.property', 'tenant:id,name,email']);
    }

    public function terminate(Lease $lease)
    {
        Gate::authorize('terminate', $lease);

        if ($lease->status !== 'active') {
            throw ValidationException::withMessages([
                'lease' => ['Only an active lease can be terminated.'],
            ]);
        }

        DB::transaction(function () use ($lease) {
            $lease->update(['status' => 'terminated']);
            $lease->unit->update(['status' => 'vacant']);
        });

        return $lease->fresh();
    }
}