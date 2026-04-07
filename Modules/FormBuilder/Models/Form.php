<?php

namespace Modules\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function fieldTypes(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'textarea' => 'Textarea',
            'select' => 'Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio',
            'number' => 'Number',
            'date' => 'Date',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest('submitted_at');
    }
}
