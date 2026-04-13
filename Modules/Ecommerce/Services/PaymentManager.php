<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Models\Payment;

class PaymentManager
{
    public function createForOrder(Order $order, string $method): Payment
    {
        $provider = match ($method) {
            'razorpay' => 'razorpay',
            'stripe' => 'stripe',
            default => 'cod',
        };

        $status = $method === 'cod' ? 'pending' : 'initiated';

        return $order->payments()->create([
            'provider' => $provider,
            'method' => $method,
            'status' => $status,
            'transaction_reference' => strtoupper($provider) . '-' . Str::upper(Str::random(12)),
            'amount' => $order->grand_total,
            'payload' => $this->providerPayload($order, $method),
            'paid_at' => $method === 'cod' ? null : now(),
        ]);
    }

    public function providerPayload(Order $order, string $method): array
    {
        return match ($method) {
            'razorpay' => [
                'public_key' => config('services.razorpay.key'),
                'amount_paise' => (int) round((float) $order->grand_total * 100),
                'currency' => 'INR',
                'note' => 'Razorpay SDK/webhook verification should be attached here.',
            ],
            'stripe' => [
                'public_key' => config('services.stripe.key'),
                'amount_cents' => (int) round((float) $order->grand_total * 100),
                'currency' => 'usd',
                'note' => 'Stripe Checkout or PaymentIntent confirmation should be attached here.',
            ],
            default => [
                'instructions' => 'Cash on delivery selected. Collect payment when the order is fulfilled.',
            ],
        };
    }

    public function markSucceeded(Payment $payment, ?string $reference = null): Payment
    {
        $payment->update([
            'status' => 'paid',
            'transaction_reference' => $reference ?: $payment->transaction_reference,
            'paid_at' => now(),
        ]);

        $payment->order()->update(['payment_status' => 'paid']);

        return $payment->fresh();
    }
}
