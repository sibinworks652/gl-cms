<?php

namespace Modules\Careers\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
