<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        return Invoice::with(['lease.unit.property:id,name', 'lease.tenant:id,name'])
            ->whereHas('lease.unit.property', fn ($q) => $q->where('landlord_id', $request->user()->id))
            ->orderByDesc('period')
            ->get();
    }

    public function myInvoices(Request $request)
    {
        return Invoice::with('lease.unit.property:id,name')
            ->whereHas('lease', fn ($q) => $q->where('tenant_id', $request->user()->id))
            ->orderByDesc('period')
            ->get();
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return $invoice->load('lease.unit.property');
    }
}