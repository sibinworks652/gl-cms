<?php

namespace Modules\Careers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'name',
        'email',
        'phone',
        'resume_path',
        'resume_original_name',
        'cover_letter',
        'linkedin_url',
        'status',
    ];

    public static function statusOptions(): array
    {
        return [
            'new' => 'New',
            'shortlisted' => 'Shortlisted',
            'rejected' => 'Rejected',
            'hired' => 'Hired',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest();
    }
}
