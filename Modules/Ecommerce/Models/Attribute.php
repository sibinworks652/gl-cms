<?php

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function isColorType(): bool
    {
        return $this->type === 'color';
    }
}
