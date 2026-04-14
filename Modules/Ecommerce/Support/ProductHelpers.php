<?php

use Illuminate\Support\Collection;
use Modules\Ecommerce\Models\Inventory;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;

if (!function_exists('get_low_stock_products')) {
    function get_low_stock_products(int $limit = 10): Collection
    {
        return Product::query()
            ->where('status', true)
            ->where(function ($query) {
                $query->whereRaw('stock <= low_stock_threshold')
                    ->orWhereHas('variants', fn($q) => $q->whereRaw('stock <= low_stock_threshold'));
            })
            ->with(['category', 'variants'])
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('get_low_stock_inventory')) {
    function get_low_stock_inventory(int $limit = 10): Collection
    {
        return Inventory::query()
            ->lowStock()
            ->with(['product', 'variant'])
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('get_out_of_stock_products')) {
    function get_out_of_stock_products(int $limit = 10): Collection
    {
        return Product::query()
            ->where('status', true)
            ->where(function ($query) {
                $query->where('stock', '<=', 0)
                    ->orWhereHas('variants', fn($q) => $q->where('stock', '<=', 0));
            })
            ->with(['category', 'variants'])
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('get_inventory_alerts')) {
    function get_inventory_alerts(): array
    {
        $lowStock = Inventory::query()->lowStock()->count();
        $outOfStock = Inventory::query()->outOfStock()->count();
        
        $products = Product::query()
            ->where('status', true)
            ->whereRaw('(stock > 0 AND stock <= COALESCE(low_stock_threshold, 10))')
            ->orWhere(function ($q) {
                $q->where('status', true)
                    ->whereHas('variants', fn($v) => $v->whereRaw('stock <= COALESCE(low_stock_threshold, 10)'));
            })
            ->count();
        
        return [
            'total' => $lowStock + $outOfStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'product_alerts' => $products,
        ];
    }
}