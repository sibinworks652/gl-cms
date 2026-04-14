<?php

namespace Modules\Ecommerce\Support;

use Modules\Settings\Models\Setting;

class EcommerceSettings
{
    public static function vendorModuleEnabled(): bool
    {
        return self::boolean('ecommerce_vendor_module_enabled', true);
    }

    public static function vendorAutoApprove(): bool
    {
        return self::boolean('ecommerce_vendor_auto_approve', false);
    }

    public static function vendorDefaultCommissionRate(): float
    {
        $value = (float) Setting::value('ecommerce_vendor_default_commission_rate', '10');

        if ($value < 0) {
            return 0.0;
        }

        if ($value > 100) {
            return 100.0;
        }

        return round($value, 2);
    }

    public static function paymentMethodEnabled(string $method): bool
    {
        return match ($method) {
            'cod' => self::boolean('ecommerce_payment_cod_enabled', true),
            'razorpay' => self::boolean('ecommerce_payment_razorpay_enabled', true),
            'stripe' => self::boolean('ecommerce_payment_stripe_enabled', true),
            'paypal' => self::boolean('ecommerce_payment_paypal_enabled', false),
            'paystack' => self::boolean('ecommerce_payment_paystack_enabled', false),
            default => false,
        };
    }

    public static function enabledPaymentMethods(): array
    {
        return collect(['cod', 'razorpay', 'stripe', 'paypal', 'paystack'])
            ->filter(fn ($method) => self::paymentMethodEnabled($method))
            ->values()
            ->all();
    }

    public static function paymentCurrency(string $default = 'USD'): string
    {
        $currency = strtoupper(trim((string) Setting::value('ecommerce_payment_default_currency', $default)));

        return $currency !== '' ? $currency : strtoupper($default);
    }

    protected static function boolean(string $key, bool $default): bool
    {
        return in_array((string) Setting::value($key, $default ? '1' : '0'), ['1', 'true', 'on', 'yes'], true);
    }
}
