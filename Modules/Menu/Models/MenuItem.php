<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'target',
        'css_class',
        'open_in_new_tab',
        'sort_order',
    ];

    public function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
        ];
    }

    public static function linkTypes(): array
    {
        return [
            'page' => 'Page',
            'custom' => 'Custom URL',
            'module' => 'Module',
        ];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function getResolvedUrlAttribute(): string
    {
        $target = trim((string) $this->target);

        if ($target === '') {
            return '#';
        }

        if ($this->type === 'module' && Route::has($target)) {
            return route($target);
        }

        if (Str::startsWith($target, ['http://', 'https://', 'mailto:', 'tel:', '#', '/'])) {
            return $target;
        }

        return url($target);
    }
}
