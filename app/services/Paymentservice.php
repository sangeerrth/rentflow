<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Mark a payment (and its invoice) as paid.
     * Safe to call many times: the second call does nothing.
     */
    public function markPaid(int $paymentId, string $razorpayPaymentId): bool
    {
        return DB::transaction(function () use ($paymentId, $razorpayPaymentId) {
            $payment = Payment::with('invoice')
                ->lockForUpdate()
                ->findOrFail($paymentId);

            if ($payment->status === 'paid') {
                return false; // already handled
            }

            $payment->update([
                'status'              => 'paid',
                'razorpay_payment_id' => $razorpayPaymentId,
                'paid_at'             => now(),
            ]);

            $payment->invoice->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            return true;
        });
    }
}