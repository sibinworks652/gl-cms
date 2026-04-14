<?php

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'price',
        'stock',
        'status',
        'track_inventory',
        'low_stock_threshold',
        'allow_backorder',
        'options',
    ];

    protected $appends = [
        'label',
        'display_options',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'boolean',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'integer',
            'allow_backorder' => 'boolean',
            'options' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'product_variant_options')
            ->withPivot('attribute_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class)->withDefault([
            'sku' => $this->sku,
            'quantity' => $this->stock,
            'track_inventory' => $this->track_inventory ?? true,
            'low_stock_threshold' => $this->low_stock_threshold ?? 10,
            'allow_backorder' => $this->allow_backorder ?? false,
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function getLabelAttribute(): string
    {
        if ($this->relationLoaded('attributeOptions') && $this->attributeOptions->isNotEmpty()) {
            return $this->attributeOptions
                ->sortBy(fn ($option) => $option->pivot?->attribute_id)
                ->pluck('name')
                ->implode(' / ');
        }

        if ($this->options) {
            return collect($this->options)->implode(' / ');
        }

        return collect([$this->color, $this->size])->filter()->implode(' / ') ?: ($this->sku ?? 'Default');
    }

    public function getDisplayOptionsAttribute(): array
    {
        if ($this->relationLoaded('attributeOptions') && $this->attributeOptions->isNotEmpty()) {
            return $this->attributeOptions->map(fn ($option) => [
                'attribute_id' => $option->attribute_id,
                'attribute_name' => $option->attribute?->name,
                'attribute_type' => $option->attribute?->type,
                'id' => $option->id,
                'name' => $option->name,
                'value' => $option->value,
            ])->values()->all();
        }

        return $this->options ?? [];
    }

    public function getTotalStockAttribute(): int
    {
        return $this->inventory ? $this->inventory->available_quantity : $this->stock;
    }

    public function isInStock(): bool
    {
        if ($inventory = $this->inventory) {
            return $inventory->canFulfill(1);
        }
        return $this->stock > 0 || $this->allow_backorder;
    }

    public function isLowStock(): bool
    {
        if ($inventory = $this->inventory) {
            return $inventory->isLowStock();
        }
        return $this->stock <= ($this->low_stock_threshold ?? 10);
    }

    public static function generateVariantCombinations(array $attributeOptions): array
    {
        if (empty($attributeOptions)) {
            return [];
        }

        $combinations = [[]];
        foreach ($attributeOptions as $attributeId => $optionIds) {
            $newCombinations = [];
            foreach ($combinations as $combo) {
                foreach ($optionIds as $optionId) {
                    $newCombinations[] = array_merge($combo, [$attributeId => $optionId]);
                }
            }
            $combinations = $newCombinations;
        }

        return $combinations;
    }

    public static function generateVariants(Product $product, array $attributeIds): array
    {
        $attributes = Attribute::whereIn('id', $attributeIds)->with('options')->get();
        
        if ($attributes->isEmpty()) {
            return [];
        }

        $optionMap = [];
        $attributeOptions = [];
        
        foreach ($attributes as $attribute) {
            $optionMap[$attribute->id] = $attribute->options->pluck('id')->toArray();
            $attributeOptions[$attribute->id] = $attribute->options->pluck('name', 'id')->toArray();
        }

        $combinations = self::generateVariantCombinations($optionMap);
        
        $variants = [];
        foreach ($combinations as $combo) {
            $options = [];
            $labels = [];
            $skuSuffix = [];
            
            foreach ($combo as $attributeId => $optionId) {
                $option = AttributeOption::find($optionId);
                if ($option) {
                    $attribute = Attribute::find($attributeId);
                    $options[$attributeId] = $optionId;
                    $labels[] = $option->name;
                    $skuSuffix[] = strtoupper(substr($attribute->name, 0, 2)) . '-' . strtoupper(substr($option->name, 0, 2));
                }
            }

            $baseSku = $product->sku ?? 'SKU';
            $variants[] = [
                'sku' => $baseSku . '-' . implode('-', $skuSuffix),
                'options' => $options,
                'label' => implode(' / ', $labels),
                'price' => $product->sale_price ?? $product->base_price,
                'stock' => 0,
                'status' => true,
            ];
        }

        return $variants;
    }
}
