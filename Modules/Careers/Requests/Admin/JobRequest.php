<?php

namespace Modules\Careers\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Careers\Models\Job;

class JobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $job = $this->route('job');
        $jobId = is_object($job) ? $job->id : $job;

        return [
            'category_id' => ['nullable', Rule::exists('job_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('career_jobs', 'slug')->ignore($jobId)],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', Rule::in(array_keys(Job::jobTypes()))],
            'experience' => ['required', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'vacancies' => ['required', 'integer', 'min:1'],
            'short_description' => ['required', 'string', 'max:1000'],
            'description' => ['required', 'string'],
            'skills' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(Job::statusOptions()))],
            'expiry_date' => ['nullable', 'date'],
        ];
    }
}
