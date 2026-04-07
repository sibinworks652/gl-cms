<?php

namespace Modules\Backup\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupFile extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'disk',
        'path',
        'size',
        'google_uploaded',
        'google_path',
        'google_error',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'google_uploaded' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
