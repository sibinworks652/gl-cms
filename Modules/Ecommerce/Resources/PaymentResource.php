<?php

namespace Modules\Ecommerce\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'method' => $this->method,
            'status' => $this->status,
            'transaction_reference' => $this->transaction_reference,
            'amount' => $this->amount,
            'payload' => $this->payload,
            'paid_at' => optional($this->paid_at)?->toISOString(),
        ];
    }
}
