<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'service_category_id',
        'title',
        'slug',
        'short_description',
        'full_description',
        'icon',
        'image_path',
        'meta_title',
        'meta_description',
        'cta_label',
        'cta_url',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getRenderedIconAttribute(): ?string
    {
        $icon = trim((string) $this->icon);

        if ($icon === '') {
            return null;
        }

        if (str_starts_with($icon, '<svg')) {
            $icon = preg_replace('/\son\w+="[^"]*"/i', '', $icon) ?? $icon;
            $icon = preg_replace("/\son\w+='[^']*'/i", '', $icon) ?? $icon;
            $icon = preg_replace('/javascript:/i', '', $icon) ?? $icon;

            return strip_tags($icon, '<svg><path><circle><rect><line><polyline><polygon><g><defs><clipPath><ellipse><title>');
        }

        if (str_contains($icon, ':')) {
            return '<iconify-icon icon="' . e($icon) . '" width="34" height="34"></iconify-icon>';
        }

        return '<i class="' . e($icon) . '"></i>';
    }
}
