<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGateway;
use Razorpay\Api\Api;

class RazorpayGateway implements PaymentGateway
{
    public function createOrder(int $amountInPaise, string $receipt): array
    {
        $api = new Api(
            (string) config('services.razorpay.key'),
            (string) config('services.razorpay.secret')
        );

        $order = $api->order->create([
            'amount'   => $amountInPaise,
            'currency' => 'INR',
            'receipt'  => $receipt,
        ]);

        return [
            'id'       => $order['id'],
            'amount'   => $order['amount'],
            'currency' => $order['currency'],
        ];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            (string) config('services.razorpay.secret')
        );

        return hash_equals($expected, $signature);
    }

    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        $expected = hash_hmac(
            'sha256',
            $rawBody,
            (string) config('services.razorpay.webhook_secret')
        );

        return hash_equals($expected, $signature);
    }
}