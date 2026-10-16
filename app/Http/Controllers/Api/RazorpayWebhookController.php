<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request, PaymentService $service)
    {
        $body      = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');

        $expected = hash_hmac('sha256', $body, config('services.razorpay.webhook_secret'));

        if (! hash_equals($expected, $signature)) {
            Log::warning('Razorpay webhook: bad signature');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($body, true);

        if (($event['event'] ?? null) === 'payment.captured') {
            $entity = data_get($event, 'payload.payment.entity');

            $payment = Payment::where('razorpay_order_id', $entity['order_id'] ?? '')->first();

            if ($payment && (int) round($payment->amount * 100) === (int) ($entity['amount'] ?? 0)) {
                $service->markPaid($payment->id, $entity['id']);
            } else {
                Log::warning('Razorpay webhook: unknown order or amount mismatch', ['entity' => $entity]);
            }
        }

        // Always answer 200 quickly so Razorpay stops retrying
        return response()->json(['ok' => true]);
    }
}