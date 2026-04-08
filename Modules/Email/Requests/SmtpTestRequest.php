<?php

namespace Modules\Email\Requests;

use Illuminate\Foundation\Http\FormRequest;
class SmtpTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [];
    }
}
