<?php

namespace Modules\Team\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'designation',
        'image',
        'short_bio',
        'description',
        'email',
        'phone',
        'social_links',
        'meta_title',
        'meta_description',
        'is_featured',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TeamDepartment::class, 'department_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'TM';
    }

    public function socialLink(string $key): ?string
    {
        $value = data_get($this->social_links, $key);
        $value = is_string($value) ? trim($value) : null;

        return $value !== '' ? $value : null;
    }
}
