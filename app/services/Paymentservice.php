<?php

namespace App\Services;

use App\Mail\PaymentReceipt;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    /**
     * Mark a payment (and its invoice) as paid.
     * Safe to call many times: the second call does nothing.
     */
    public function markPaid(int $paymentId, string $razorpayPaymentId): bool
    {
        $newlyPaid = DB::transaction(function () use ($paymentId, $razorpayPaymentId) {
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

        // Only after the transaction has committed
        if ($newlyPaid) {
            $this->sendReceipts($paymentId);
        }

        return $newlyPaid;
    }

    private function sendReceipts(int $paymentId): void
    {
        $payment = Payment::with('invoice.lease.tenant', 'invoice.lease.unit.property.landlord')
            ->findOrFail($paymentId);

        $lease = $payment->invoice->lease;

        Mail::to($lease->tenant)->queue(new PaymentReceipt($payment, forLandlord: false));
        Mail::to($lease->unit->property->landlord)->queue(new PaymentReceipt($payment, forLandlord: true));
    }
}