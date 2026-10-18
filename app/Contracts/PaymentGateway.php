<?php

namespace App\Contracts;

interface PaymentGateway
{
    /** @return array{id: string, amount: int, currency: string} */
    public function createOrder(int $amountInPaise, string $receipt): array;

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool;

    public function verifyWebhook(string $rawBody, string $signature): bool;
}