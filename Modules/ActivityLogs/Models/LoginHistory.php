<?php

namespace Modules\ActivityLogs\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'admin_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('login_at');
    }
}
