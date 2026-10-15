<?php

namespace App\Console\Commands;

use App\Jobs\ApplyLateFee;
use App\Models\Invoice;
use Illuminate\Console\Command;

class ApplyLateFees extends Command
{
    protected $signature = 'invoices:apply-late-fees';

    protected $description = 'Queue late fees for pending invoices past their grace period';

    public function handle(): int
    {
        $ids = Invoice::query()
            ->join('leases', 'leases.id', '=', 'invoices.lease_id')
            ->where('invoices.status', 'pending')
            ->whereRaw('DATE_ADD(invoices.due_date, INTERVAL leases.grace_days DAY) < ?', [today()->toDateString()])
            ->pluck('invoices.id');

        foreach ($ids as $id) {
            ApplyLateFee::dispatch($id);
        }

        $this->info("Late fee jobs queued: {$ids->count()}");

        return self::SUCCESS;
    }
}