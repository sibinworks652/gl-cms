<?php

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'sku',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
        'track_inventory',
        'allow_backorder',
        'last_restock_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'last_restock_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class)->latest();
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isLowStock(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }
        return $this->available_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }
        return $this->available_quantity <= 0 && ! $this->allow_backorder;
    }

    public function canFulfill(int $quantity): bool
    {
        if (! $this->track_inventory) {
            return true;
        }
        if ($this->allow_backorder) {
            return true;
        }
        return $this->available_quantity >= $quantity;
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) > 0');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= low_stock_threshold')
            ->where('track_inventory', true);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= 0')
            ->where('track_inventory', true)
            ->where('allow_backorder', false);
    }

    public function adjustQuantity(int $change, string $type, ?int $orderId = null, ?string $reference = null, ?string $notes = null): self
    {
        return DB::transaction(function () use ($change, $type, $orderId, $reference, $notes) {
            $before = $this->quantity;
            $after = max(0, $this->quantity + $change);

            $this->update(['quantity' => $after]);

            InventoryLog::create([
                'inventory_id' => $this->id,
                'order_id' => $orderId,
                'type' => $type,
                'quantity_change' => $change,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_number' => $reference,
                'notes' => $notes,
            ]);

            if ($change < 0) {
                $this->logs()->create([
                    'type' => $type,
                    'quantity_change' => $change,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reference_number' => $reference,
                    'notes' => $notes,
                ]);
            }

            return $this->fresh();
        });
    }

    public function reserve(int $quantity, ?int $orderId = null): bool
    {
        if (! $this->canFulfill($quantity)) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);

        $this->logs()->create([
            'order_id' => $orderId,
            'type' => 'reserve',
            'quantity_change' => -$quantity,
            'quantity_before' => $this->reserved_quantity - $quantity,
            'quantity_after' => $this->reserved_quantity,
        ]);

        return true;
    }

    public function release(int $quantity): self
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));

        return $this->fresh();
    }

    public function fulfill(int $quantity): self
    {
        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));

        return $this->fresh();
    }

    public function restock(int $quantity, ?string $notes = null): self
    {
        $before = $this->quantity;
        $this->increment('quantity', $quantity);
        $this->update(['last_restock_at' => now()]);

        $this->logs()->create([
            'type' => 'restock',
            'quantity_change' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $this->quantity,
            'notes' => $notes ?: 'Manual restock',
        ]);

        return $this->fresh();
    }
}
