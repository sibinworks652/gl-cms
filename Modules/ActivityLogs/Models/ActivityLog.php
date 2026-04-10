<?php

namespace Modules\ActivityLogs\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'record_type',
        'record_id',
        'record_title',
        'description',
        'route_name',
        'related_url',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    public function badgeClass(): string
    {
        return match ($this->action) {
            'create', 'login' => 'success',
            'update' => 'info',
            'delete', 'logout' => 'danger',
            default => 'secondary',
        };
    }

    public function actionLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->action));
    }
}
