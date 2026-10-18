<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function createOrder(Invoice $invoice, PaymentGateway $gateway)
    {
        Gate::authorize('pay', $invoice);

        if (! in_array($invoice->status, ['pending', 'overdue'])) {
            throw ValidationException::withMessages([
                'invoice' => ['This invoice cannot be paid (status: ' . $invoice->status . ').'],
            ]);
        }

        $total = (float) $invoice->amount + (float) $invoice->late_fee;

        $order = $gateway->createOrder(
            (int) round($total * 100),
            'inv-' . $invoice->id . '-' . now()->timestamp
        );

        $payment = $invoice->payments()->create([
            'razorpay_order_id' => $order['id'],
            'amount'            => $total,
            'status'            => 'created',
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'order_id'   => $order['id'],
            'amount'     => $order['amount'],
            'currency'   => $order['currency'],
            'gateway'    => config('services.payment_gateway'),
            'key'        => config('services.razorpay.key'),
        ], 201);
    }

    public function verify(Request $request, PaymentGateway $gateway, PaymentService $service)
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])
            ->with('invoice.lease')
            ->firstOrFail();

        Gate::authorize('pay', $payment->invoice);

        if (! $gateway->verifyPayment(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature']
        )) {
            throw ValidationException::withMessages([
                'razorpay_signature' => ['Payment signature is invalid.'],
            ]);
        }

        $service->markPaid($payment->id, $data['razorpay_payment_id']);

        return response()->json(['message' => 'Payment verified', 'status' => 'paid']);
    }
}