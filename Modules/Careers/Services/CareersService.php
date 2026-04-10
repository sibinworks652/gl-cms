<?php

namespace Modules\Careers\Services;

use App\Support\ModuleRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Careers\Models\Job;
use Modules\Careers\Models\JobApplication;
use Modules\Careers\Models\JobCategory;
use Modules\Settings\Models\Setting;

class CareersService
{
    public function settings(): array
    {
        return Setting::pairs();
    }
    public function categories(bool $activeOnly = false): Collection
    {
        return JobCategory::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->ordered()
            ->get();
    }

    public function adminJobs(array $filters = []): Collection
    {
        return Job::query()
            ->with('category')
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['job_type'] ?? null, fn ($query, $jobType) => $query->where('job_type', $jobType))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%')
                        ->orWhere('experience', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function frontendJobs(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        return Job::query()
            ->with('category')
            ->open()
            ->when($filters['category'] ?? null, function ($query, string $categorySlug) {
                $query->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug));
            })
            ->when($filters['location'] ?? null, fn ($query, $location) => $query->where('location', $location))
            ->when($filters['job_type'] ?? null, fn ($query, $jobType) => $query->where('job_type', $jobType))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('short_description', 'like', '%' . $search . '%')
                        ->orWhere('skills', 'like', '%' . $search . '%')
                        ->orWhere('requirements', 'like', '%' . $search . '%');
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function locations(): array
    {
        return Job::query()
            ->open()
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->filter()
            ->values()
            ->all();
    }

    public function featuredJobs(int $limit = 6): Collection
    {
        return Job::query()
            ->with('category')
            ->open()
            ->where('is_featured', true)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function findFrontendJob(string $slug): Job
    {
        return Job::query()
            ->with('category')
            ->open()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function createJob(array $data): Job
    {
        return Job::create($this->jobPayload($data));
    }

    public function updateJob(Job $job, array $data): Job
    {
        $job->update($this->jobPayload($data, $job));

        return $job->fresh('category');
    }

    public function deleteJob(Job $job): void
    {
        foreach ($job->applications as $application) {
            if ($application->resume_path) {
                Storage::disk('local')->delete($application->resume_path);
            }
        }

        $job->delete();
    }

    public function createCategory(array $data): JobCategory
    {
        return JobCategory::create($this->categoryPayload($data));
    }

    public function updateCategory(JobCategory $category, array $data): JobCategory
    {
        $category->update($this->categoryPayload($data, $category));

        return $category->fresh();
    }

    public function applications(array $filters = []): Collection
    {
        return JobApplication::query()
            ->with(['job.category'])
            ->when($filters['job'] ?? null, fn ($query, $jobId) => $query->where('job_id', $jobId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->latestFirst()
            ->get();
    }

    public function submitApplication(Job $job, array $data, ?UploadedFile $resume = null): JobApplication
    {
        $application = DB::transaction(function () use ($job, $data, $resume) {
            $application = $job->applications()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'resume_path' => $resume ? $resume->storeAs('careers/resumes', $this->datedOriginalFilename($resume), 'local') : null,
                'resume_original_name' => $resume?->getClientOriginalName(),
                'cover_letter' => $data['cover_letter'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'status' => 'new',
            ]);

            return $application->fresh('job');
        }, 3);

        $this->sendApplicationNotifications($application);

        return $application;
    }

    public function updateApplicationStatus(JobApplication $application, string $status): JobApplication
    {
        $application->update([
            'status' => $status,
        ]);

        return $application->fresh('job');
    }

    public function resumeDownloadPath(JobApplication $application): string
    {
        return Storage::disk('local')->path($application->resume_path);
    }

    protected function jobPayload(array $data, ?Job $job = null): array
    {
        return [
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug(Job::class, $data['slug'] ?? $data['title'], $job?->id),
            'location' => $data['location'],
            'job_type' => $data['job_type'],
            'experience' => $data['experience'],
            'salary' => $data['salary'] ?? null,
            'vacancies' => $data['vacancies'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'skills' => $data['skills'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'responsibilities' => $data['responsibilities'] ?? null,
            'benefits' => $data['benefits'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'status' => $data['status'],
            'expiry_date' => $data['expiry_date'] ?? null,
        ];
    }

    protected function categoryPayload(array $data, ?JobCategory $category = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(JobCategory::class, $data['slug'] ?? $data['name'], $category?->id),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    protected function datedOriginalFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'resume';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }

    protected function sendApplicationNotifications(JobApplication $application): void
    {
        $application->loadMissing('job');
        $settings = $this->settings();

        $data = [
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'status' => Str::headline($application->status),
            'job_title' => $application->job->title,
            'job_type' => Job::jobTypes()[$application->job->job_type] ?? $application->job->job_type,
            'job_location' => $application->job->location,
            'job_url' => route('careers.show', $application->job->slug),
            'careers_url' => route('careers.index'),
            'resume_filename' => $application->resume_original_name ?: basename((string) $application->resume_path),
            'linkedin_url' => $application->linkedin_url ?: '-',
            'cover_letter' => $application->cover_letter ?: '-',
            'button_url' => route('admin.applications.show', $application),
            'date' => now()->format('d M Y H:i'),
            'site_name' => $settings['site_name'] ?? config('app.name'),
        ];

        $this->sendTemplateMail('career-application-confirmation', $application->email, $data);

        $adminRecipient = $this->adminNotificationRecipients($settings);

        if ($adminRecipient === []) {
            Log::warning('Career admin notification skipped because no admin recipient is configured.', [
                'application_id' => $application->id,
                'job_id' => $application->job_id,
            ]);

            return;
        }

        $this->sendTemplateMail('career-application-admin-notification', $adminRecipient, $data);
    }

    protected function sendTemplateMail(string $slug, string|array $recipient, array $data): void
    {
        if (! $this->emailModuleAvailable()) {
            return;
        }

        $emailTemplateClass = \Modules\Email\Models\EmailTemplate::class;
        $template = $emailTemplateClass::query()
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $template) {
            return;
        }

        try {
            $this->emailService()?->sendTemplate($template, $data, $recipient);
        } catch (\Throwable $exception) {
            Log::warning('Career email notification failed.', [
                'template' => $slug,
                'recipient' => $recipient,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function adminNotificationRecipients(array $settings): array
    {
        $candidate = $settings['site_email']
            ?? $settings['mail_from_address']
            ?? config('mail.enquiry_to')
            ?? config('mail.from.address');

        if (blank($candidate)) {
            return [];
        }

        return collect(preg_split('/[\r\n,;]+/', (string) $candidate) ?: [])
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    protected function emailService(): ?object
    {
        $class = \Modules\Email\Services\EmailService::class;

        if (! $this->emailModuleAvailable() || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }

    protected function emailModuleAvailable(): bool
    {
        return ModuleRegistry::enabled('email')
            && class_exists(\Modules\Email\Services\EmailService::class)
            && class_exists(\Modules\Email\Models\EmailTemplate::class);
    }
}
