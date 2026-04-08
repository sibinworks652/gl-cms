<?php

namespace Modules\Email\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuilderImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:4096'],
        ];
    }
}
