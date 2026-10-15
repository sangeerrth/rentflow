<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'lease_id', 'period', 'amount', 'late_fee',
        'due_date', 'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period'   => 'date',
            'due_date' => 'date',
            'paid_at'  => 'datetime',
            'amount'   => 'decimal:2',
            'late_fee' => 'decimal:2',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }
}