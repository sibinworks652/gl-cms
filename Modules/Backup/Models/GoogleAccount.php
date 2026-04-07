<?php

namespace Modules\Backup\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAccount extends Model
{
    protected $fillable = [
        'user_id',
        'google_email',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'drive_folder_id',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'access_token_expires_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function accessTokenExpired(): bool
    {
        return ! $this->access_token
            || ! $this->access_token_expires_at
            || $this->access_token_expires_at->lte(now()->addMinute());
    }
}
