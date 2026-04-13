<?php

namespace Modules\Ecommerce\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'items_count' => $this->items_count,
            'subtotal' => $this->subtotal,
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'product' => [
                    'id' => $item->product?->id,
                    'name' => $item->product?->name,
                    'slug' => $item->product?->slug,
                    'featured_image_url' => $item->product?->featured_image_url,
                    'vendor' => $item->product?->vendor?->name,
                ],
                'variant' => $item->variant ? [
                    'id' => $item->variant->id,
                    'label' => $item->variant->label,
                ] : null,
            ])->values(),
        ];
    }
}
