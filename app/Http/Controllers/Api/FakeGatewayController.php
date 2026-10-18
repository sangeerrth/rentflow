<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Gateways\FakeGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class FakeGatewayController extends Controller
{
    public function pay(Request $request, FakeGateway $gateway)
    {
        $data = $request->validate(['order_id' => 'required|string']);

        $payment = Payment::where('razorpay_order_id', $data['order_id'])
            ->with('invoice.lease')
            ->firstOrFail();

        Gate::authorize('pay', $payment->invoice);

        $paymentId = 'pay_fake_' . Str::random(14);

        return response()->json([
            'razorpay_order_id'   => $data['order_id'],
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $gateway->sign($data['order_id'], $paymentId),
        ]);
    }
}