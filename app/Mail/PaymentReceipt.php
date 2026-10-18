<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public bool $forLandlord = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forLandlord ? 'Rent received' : 'Your rent payment receipt',
        );
    }

    public function content(): Content
    {
        $invoice = $this->payment->invoice;
        $lease   = $invoice->lease;

        return new Content(
            markdown: 'emails.payment-receipt',
            with: [
                'forLandlord' => $this->forLandlord,
                'tenant'      => $lease->tenant,
                'unit'        => $lease->unit,
                'property'    => $lease->unit->property,
                'period'      => $invoice->period->format('F Y'),
                'amount'      => number_format((float) $this->payment->amount, 2),
                'reference'   => $this->payment->razorpay_payment_id,
                'paidAt'      => $this->payment->paid_at?->format('d M Y, h:i A'),
            ],
        );
    }
}