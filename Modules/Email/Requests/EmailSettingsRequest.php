<?php

namespace Modules\Email\Requests;

use Illuminate\Foundation\Http\FormRequest;
class EmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'email_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'email_header' => ['nullable', 'string', 'max:20000'],
            'email_footer' => ['nullable', 'string', 'max:20000'],
            'email_signature' => ['nullable', 'string', 'max:10000'],
            'email_theme_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'email_text_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ];
    }
}
