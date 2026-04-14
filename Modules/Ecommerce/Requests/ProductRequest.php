<?php

namespace Modules\Ecommerce\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (auth('admin')->check()) {
            return true;
        }

        return auth()->check() && request()->routeIs('vendor.*');
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_weight' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'delivery_rules' => ['nullable', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'gallery_images.*' => ['nullable', 'image', 'max:4096'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            'track_inventory' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'allow_backorder' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.size' => ['nullable', 'string', 'max:100'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.status' => ['nullable', 'boolean'],
            'variants.*.track_inventory' => ['nullable', 'boolean'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'variants.*.allow_backorder' => ['nullable', 'boolean'],
            'variants.*.attribute_option_ids' => ['nullable', 'array'],
            'variants.*.attribute_option_ids.*' => ['integer', 'exists:attribute_options,id'],
            'variants.*.label' => ['nullable', 'array'],
            'variants.*.label.*' => ['string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'attribute_ids' => ['nullable', 'array'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
            'attribute_option_ids' => ['nullable', 'array'],
            'attribute_option_ids.*' => ['integer', 'exists:attribute_options,id'],
        ];
    }
}
