<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate {--month= : Month to bill, like 2026-11}';

    protected $description = 'Create rent invoices for all active leases';

    public function handle(): int
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('!Y-m', $this->option('month'))
            : now()->startOfMonth();

        $created = 0;

        Lease::where('status', 'active')
            ->whereDate('start_date', '<=', $month->copy()->endOfMonth())
            ->whereDate('end_date', '>=', $month)
            ->each(function (Lease $lease) use ($month, &$created) {
                $invoice = Invoice::firstOrCreate(
                    [
                        'lease_id' => $lease->id,
                        'period'   => $month->toDateString(),
                    ],
                    [
                        'amount'   => $lease->rent_amount,
                        'due_date' => $month->copy()->day($lease->rent_due_day)->toDateString(),
                        'status'   => 'pending',
                    ]
                );

                if ($invoice->wasRecentlyCreated) {
                    $created++;
                }
            });

        $this->info("Invoices created for {$month->format('F Y')}: {$created}");

        return self::SUCCESS;
    }
}