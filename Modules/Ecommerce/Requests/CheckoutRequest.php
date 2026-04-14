<?php

namespace Modules\Ecommerce\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Ecommerce\Support\EcommerceSettings;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'shipping_address' => ['required', 'string'],
            'billing_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:' . implode(',', EcommerceSettings::enabledPaymentMethods())],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
