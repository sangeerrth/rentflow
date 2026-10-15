<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ApplyLateFee implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $invoiceId)
    {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $invoice = Invoice::with('lease')
                ->lockForUpdate()
                ->find($this->invoiceId);

            // Already handled, paid or cancelled: do nothing
            if (! $invoice || $invoice->status !== 'pending') {
                return;
            }

            $graceEnds = $invoice->due_date->copy()->addDays($invoice->lease->grace_days);

            // Still inside the grace period: do nothing
            if (today()->lte($graceEnds)) {
                return;
            }

            $invoice->update([
                'status'   => 'overdue',
                'late_fee' => $invoice->lease->late_fee_amount,
            ]);
        });
    }
}