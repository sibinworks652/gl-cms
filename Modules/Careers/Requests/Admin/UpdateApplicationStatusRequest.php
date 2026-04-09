<?php

namespace Modules\Careers\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Careers\Models\JobApplication;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(JobApplication::statusOptions()))],
        ];
    }
}
