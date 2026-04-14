<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Models\Payment;
use Modules\Ecommerce\Support\EcommerceSettings;

class PaymentManager
{
    public function createForOrder(Order $order, string $method): Payment
    {
        abort_unless(EcommerceSettings::paymentMethodEnabled($method), 422, 'Selected payment method is currently unavailable.');

        $provider = match ($method) {
            'razorpay' => 'razorpay',
            'stripe' => 'stripe',
            'paypal' => 'paypal',
            'paystack' => 'paystack',
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
        $currency = EcommerceSettings::paymentCurrency('USD');

        return match ($method) {
            'razorpay' => [
                'public_key' => config('services.razorpay.key'),
                'amount_paise' => (int) round((float) $order->grand_total * 100),
                'currency' => $currency,
                'note' => 'Razorpay SDK/webhook verification should be attached here.',
            ],
            'stripe' => [
                'public_key' => config('services.stripe.key'),
                'amount_cents' => (int) round((float) $order->grand_total * 100),
                'currency' => strtolower($currency),
                'note' => 'Stripe Checkout or PaymentIntent confirmation should be attached here.',
            ],
            'paypal' => [
                'client_id' => config('services.paypal.client_id'),
                'mode' => config('services.paypal.mode', 'sandbox'),
                'amount' => (float) $order->grand_total,
                'currency' => $currency,
                'note' => 'PayPal order creation/capture webhook flow should be attached here.',
            ],
            'paystack' => [
                'public_key' => config('services.paystack.public_key'),
                'amount_kobo' => (int) round((float) $order->grand_total * 100),
                'currency' => $currency,
                'note' => 'Paystack transaction initialization/verification should be attached here.',
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
