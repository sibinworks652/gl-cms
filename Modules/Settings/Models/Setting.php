<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected const CACHE_KEY = 'settings.pairs';

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function pairs(): array
    {
        if (! static::isCacheEnabled()) {
            static::clearCache();

            return static::freshPairs();
        }

        return Cache::rememberForever(static::CACHE_KEY, fn () => static::freshPairs());
    }

    public static function freshPairs(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return static::query()
            ->pluck('value', 'key')
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::pairs()[$key] ?? $default;
    }

    protected static function isCacheEnabled(): bool
    {
        try {
            $cacheEnabled = static::query()
                ->where('key', 'cache_enabled')
                ->value('value');
        } catch (\Throwable) {
            return false;
        }

        return ! in_array((string) $cacheEnabled, ['0', 'false', 'off'], true);
    }
}
