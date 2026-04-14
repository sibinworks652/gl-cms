<?php

namespace Modules\Ecommerce\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'sale_price' => $this->sale_price,
            'display_price' => $this->display_price,
            'final_price' => $this->final_price,
            'stock' => $this->stock,
            'track_inventory' => (bool) $this->track_inventory,
            'low_stock_threshold' => $this->low_stock_threshold,
            'allow_backorder' => (bool) $this->allow_backorder,
            'tax_percentage' => $this->tax_percentage,
            'shipping_weight' => $this->shipping_weight,
            'shipping_cost' => $this->shipping_cost,
            'delivery_rules' => $this->delivery_rules,
            'featured_image_url' => $this->featured_image_url,
            'is_featured' => (bool) $this->is_featured,
            'status' => (bool) $this->status,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
                'slug' => $this->vendor?->slug,
            ]),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'label' => $variant->label,
                'size' => $variant->size,
                'color' => $variant->color,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'track_inventory' => (bool) $variant->track_inventory,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'allow_backorder' => (bool) $variant->allow_backorder,
                'options' => $variant->display_options,
                'status' => (bool) $variant->status,
            ])->values()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->url,
            ])->values()),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'type' => $tag->type,
            ])->values()),
        ];
    }
}
