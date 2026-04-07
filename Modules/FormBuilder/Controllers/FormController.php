<?php

namespace Modules\FormBuilder\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\FormSubmissionNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\FormBuilder\Models\Form;
use Modules\FormBuilder\Models\FormSubmission;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('formbuilder::index', [
            'forms' => $forms,
        ]);
    }

    public function create()
    {
        return view('formbuilder::form', [
            'form' => new Form(['is_active' => true]),
            'isEdit' => false,
            'fieldTypes' => Form::fieldTypes(),
            'schemaPayload' => '[]',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);
        $schema = $this->validateSchema($validated['schema_payload'] ?? '[]');

        Form::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'schema' => $schema,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form created successfully.');
    }

    public function show(Form $form)
    {
        return view('formbuilder::view', [
            'form' => $form,
            'recentSubmissions' => $form->submissions()->take(10)->get(),
        ]);
    }

    public function edit(Form $form)
    {
        return view('formbuilder::form', [
            'form' => $form,
            'isEdit' => true,
            'fieldTypes' => Form::fieldTypes(),
            'schemaPayload' => json_encode($form->schema ?? [], JSON_PRETTY_PRINT),
        ]);
    }

    public function update(Request $request, Form $form)
    {
        $validated = $this->validateForm($request, $form);
        $schema = $this->validateSchema($validated['schema_payload'] ?? '[]');

        $form->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'schema' => $schema,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form updated successfully.');
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form deleted successfully.');
    }

    public function showPublic(Form $form)
    {
        abort_unless($form->is_active, 404);

        return view('formbuilder::public', [
            'form' => $form,
            'fields' => $form->schema ?? [],
        ]);
    }

    public function storePublic(Request $request, Form $form)
    {
        abort_unless($form->is_active, 404);

        $fields = collect($form->schema ?? []);
        $rules = [];
        $attributeLabels = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;
            $type = $field['type'] ?? 'text';

            if (! $name) {
                continue;
            }

            $attributeLabels[$name] = $field['label'] ?? $name;
            $isRequired = ! empty($field['required']);
            $baseRules = $isRequired ? ['required'] : ['nullable'];

            switch ($type) {
                case 'email':
                    $rules[$name] = array_merge($baseRules, ['string', 'email', 'max:255']);
                    break;
                case 'number':
                    $rules[$name] = array_merge($baseRules, ['numeric']);
                    break;
                case 'date':
                    $rules[$name] = array_merge($baseRules, ['date']);
                    break;
                case 'select':
                case 'radio':
                    $rules[$name] = array_merge($baseRules, ['string', Rule::in($field['options'] ?? [])]);
                    break;
                case 'checkbox':
                    $rules[$name] = array_merge($isRequired ? ['required'] : ['nullable'], ['array']);
                    $rules[$name . '.*'] = ['string', Rule::in($field['options'] ?? [])];
                    break;
                case 'textarea':
                case 'text':
                default:
                    $rules[$name] = array_merge($baseRules, ['string', 'max:5000']);
                    break;
            }
        }

        $validated = $request->validate($rules, [], $attributeLabels);
        $payload = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $payload[] = [
                'label' => $field['label'] ?? $name,
                'name' => $name,
                'type' => $field['type'] ?? 'text',
                'value' => $validated[$name] ?? (isset($rules[$name]) && str_contains(implode('|', (array) $rules[$name]), 'array') ? [] : null),
            ];
        }

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
            'submitted_at' => Carbon::now(),
        ]);

        $recipient = config('mail.enquiry_to');

        if ($recipient) {
            try {
                Mail::to($recipient)->send(new FormSubmissionNotification($form, $submission));
            } catch (\Throwable $exception) {
                Log::warning('Form submission email failed to send.', [
                    'form_id' => $form->id,
                    'submission_id' => $submission->id,
                    'recipient' => $recipient,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('forms.public.show', $form->slug)
            ->with('success', 'Form submitted successfully. Our team has been notified by email.');
    }

    public function submissions(Form $form)
    {
        return view('formbuilder::submissions', [
            'form' => $form,
            'submissions' => $form->submissions()->paginate(20)->withQueryString(),
        ]);
    }

    protected function validateForm(Request $request, ?Form $form = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('forms', 'slug')->ignore($form?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'schema_payload' => ['nullable', 'string'],
        ]);
    }

    protected function validateSchema(string $payload): array
    {
        if (trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'schema_payload' => 'The form schema payload is invalid.',
            ]);
        }

        return collect(array_values($decoded))->map(function ($field, $index) {
            if (! is_array($field)) {
                throw ValidationException::withMessages([
                    'schema_payload' => 'Each field must be a valid object.',
                ]);
            }

            $type = (string) ($field['type'] ?? 'text');
            $label = trim((string) ($field['label'] ?? ''));
            $name = trim((string) ($field['name'] ?? ''));
            $placeholder = trim((string) ($field['placeholder'] ?? ''));
            $column = (int) ($field['column'] ?? 12);
            $options = collect($field['options'] ?? [])
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->values()
                ->all();

            if (! array_key_exists($type, Form::fieldTypes())) {
                throw ValidationException::withMessages([
                    'schema_payload' => 'A field has an invalid type.',
                ]);
            }

            if ($label === '' || $name === '') {
                throw ValidationException::withMessages([
                    'schema_payload' => 'Every field needs a label and machine name.',
                ]);
            }

            if (! in_array($column, [12, 6, 4, 3], true)) {
                throw ValidationException::withMessages([
                    'schema_payload' => 'A field has an invalid column width.',
                ]);
            }

            return [
                'id' => $field['id'] ?? ('field-' . $index),
                'type' => $type,
                'label' => Str::limit($label, 255, ''),
                'name' => Str::slug($name, '_'),
                'placeholder' => $placeholder !== '' ? Str::limit($placeholder, 255, '') : null,
                'column' => $column,
                'required' => (bool) ($field['required'] ?? false),
                'options' => in_array($type, ['select', 'checkbox', 'radio'], true) ? $options : [],
            ];
        })->all();
    }
}
