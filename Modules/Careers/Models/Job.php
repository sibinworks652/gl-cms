<?php

namespace Modules\Careers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $table = 'career_jobs';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'location',
        'job_type',
        'experience',
        'salary',
        'vacancies',
        'short_description',
        'description',
        'skills',
        'requirements',
        'responsibilities',
        'benefits',
        'meta_title',
        'meta_description',
        'is_featured',
        'status',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'expiry_date' => 'date',
            'vacancies' => 'integer',
        ];
    }

    public static function jobTypes(): array
    {
        return [
            'full-time' => 'Full-time',
            'part-time' => 'Part-time',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->orderBy('title');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(function (Builder $builder) {
                $builder
                    ->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            });
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
