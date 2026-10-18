<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request, PaymentGateway $gateway, PaymentService $service)
    {
        $body      = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');

        if (! $gateway->verifyWebhook($body, $signature)) {
            Log::warning('Payment webhook: bad signature');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($body, true);

        if (($event['event'] ?? null) === 'payment.captured') {
            $entity = data_get($event, 'payload.payment.entity');

            $payment = Payment::where('razorpay_order_id', $entity['order_id'] ?? '')->first();

            if ($payment && (int) round($payment->amount * 100) === (int) ($entity['amount'] ?? 0)) {
                $service->markPaid($payment->id, $entity['id']);
            } else {
                Log::warning('Payment webhook: unknown order or amount mismatch', ['entity' => $entity]);
            }
        }

        return response()->json(['ok' => true]);
    }
}