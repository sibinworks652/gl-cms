<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SeoSetting extends Model
{
    protected $fillable = [
        'page_type',
        'page_key',
        'page_label',
        'seo_meta_title',
        'seo_meta_description',
        'seo_meta_keywords',
        'seo_og_image',
        'seo_twitter_card',
        'seo_canonical_url',
        'seo_indexing',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function findFor(string $pageKey, string $pageType = 'page'): ?self
    {
        return self::query()
            ->where('page_type', $pageType)
            ->where('page_key', trim($pageKey, '/'))
            ->where('is_active', true)
            ->first();
    }

    public function ogImageUrl(): ?string
    {
        if (! $this->seo_og_image) {
            return null;
        }

        if (str_starts_with($this->seo_og_image, 'http://') || str_starts_with($this->seo_og_image, 'https://')) {
            return $this->seo_og_image;
        }

        return Storage::disk('public')->url($this->seo_og_image);
    }
}
