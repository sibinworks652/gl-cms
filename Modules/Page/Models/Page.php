<?php

namespace Modules\Page\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'view_path',
        'description',
        'content_mode',
        'content',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function contentModes(): array
    {
        return [
            'blade' => 'Blade View File',
            'content' => 'Rich Content',
            'html' => 'HTML / Blade Design',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
