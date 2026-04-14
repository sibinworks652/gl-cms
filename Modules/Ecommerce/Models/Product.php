<?php

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'vendor_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'base_price',
        'sale_price',
        'tax_percentage',
        'shipping_weight',
        'shipping_cost',
        'delivery_rules',
        'stock',
        'featured_image',
        'is_featured',
        'status',
        'track_inventory',
        'low_stock_threshold',
        'allow_backorder',
    ];

    protected $appends = [
        'display_price',
        'final_price',
        'featured_image_url',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'shipping_weight' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'delivery_rules' => 'array',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'integer',
            'allow_backorder' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class)->whereNull('product_variant_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_product');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes');
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'product_attribute_options');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function getDisplayPriceAttribute(): string
    {
        return number_format((float) ($this->sale_price ?: $this->base_price), 2, '.', '');
    }

    public function getFinalPriceAttribute(): string
    {
        $base = (float) ($this->sale_price ?: $this->base_price);
        $discount = app(\Modules\Ecommerce\Services\PricingManager::class)->productDiscount($this);
        $amount = $discount ? max($base - $discount->calculateAmount($base), 0) : $base;

        return number_format($amount, 2, '.', '');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->variants()->exists()) {
            return $this->variants()->sum('stock');
        }
        return $this->stock;
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function isInStock(): bool
    {
        return $this->total_stock > 0;
    }
}
