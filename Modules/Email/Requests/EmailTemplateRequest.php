<?php

namespace Modules\Email\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $template = $this->route('template');
        $templateId = is_object($template) ? $template->id : $template;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('email_templates', 'slug')->ignore($templateId)],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_]+$/'],
            'use_header' => ['nullable', 'boolean'],
            'use_footer' => ['nullable', 'boolean'],
            'use_signature' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
