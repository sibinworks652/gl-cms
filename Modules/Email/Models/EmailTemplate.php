<?php

namespace Modules\Email\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'to_emails',
        'cc_emails',
        'use_header',
        'use_footer',
        'use_signature',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'to_emails' => 'array',
            'cc_emails' => 'array',
            'use_header' => 'boolean',
            'use_footer' => 'boolean',
            'use_signature' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
