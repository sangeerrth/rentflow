<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGateway;
use Illuminate\Support\Str;

class FakeGateway implements PaymentGateway
{
    private function secret(): string
    {
        return (string) config('services.fake_secret');
    }

    public function createOrder(int $amountInPaise, string $receipt): array
    {
        return [
            'id'       => 'order_fake_' . Str::random(14),
            'amount'   => $amountInPaise,
            'currency' => 'INR',
        ];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        return hash_equals($this->sign($orderId, $paymentId), $signature);
    }

    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        return hash_equals(hash_hmac('sha256', $rawBody, $this->secret()), $signature);
    }

    /** Used by the dev "fake pay" endpoint to act like Razorpay Checkout. */
    public function sign(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, $this->secret());
    }
}