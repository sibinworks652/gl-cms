<?php

namespace Modules\Banner\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BannerSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'media_type',
        'image_path',
        'video_url',
        'button_label',
        'button_link_type',
        'button_link',
        'open_in_new_tab',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public static function mediaTypes(): array
    {
        return [
            'image' => 'Image',
            'video' => 'Video URL',
        ];
    }

    public static function linkTypes(): array
    {
        return [
            'page' => 'Internal Page',
            'custom' => 'External / Custom URL',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->active()
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function getResolvedButtonLinkAttribute(): ?string
    {
        $target = trim((string) $this->button_link);

        if ($target === '') {
            return null;
        }

        if ($this->button_link_type === 'page' && Route::has($target)) {
            return route($target);
        }

        if (Str::startsWith($target, ['http://', 'https://', 'mailto:', 'tel:', '#', '/'])) {
            return $target;
        }

        return url($target);
    }
}
