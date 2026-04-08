<?php

namespace Modules\Email\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'exists:email_templates,id'],
            'email' => [
                'required',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $emails = $this->emails((string) $value);

                    if ($emails === []) {
                        $fail('Enter at least one valid email address.');

                        return;
                    }

                    foreach ($emails as $email) {
                        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail('The email list contains an invalid address: ' . $email);

                            return;
                        }
                    }
                },
            ],
            'payload' => ['nullable', 'array'],
        ];
    }

    public function emails(?string $value = null): array
    {
        $value ??= (string) $this->input('email', '');

        return collect(preg_split('/[\s,;]+/', $value) ?: [])
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
