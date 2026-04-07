<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function locations(): array
    {
        return [
            'header' => 'Header',
            'footer' => 'Footer',
            'sidebar' => 'Sidebar',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->ordered();
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->ordered();
    }

    public static function forLocation(string $location): ?self
    {
        return static::query()
            ->active()
            ->where('location', $location)
            ->with(['rootItems.childrenRecursive'])
            ->first();
    }
}
