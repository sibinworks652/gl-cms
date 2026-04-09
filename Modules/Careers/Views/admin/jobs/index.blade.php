@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Jobs</h4>
                <p class="text-muted mb-0">Publish and manage career opportunities from one place.</p>
            </div>
            <div class="d-flex gap-2">
                @if($adminUser?->can('careers.categories.view'))
                    <a href="{{ route('admin.job-categories.index') }}" class="btn btn-light">Categories</a>
                @endif
                @if($adminUser?->can('careers.jobs.create'))
                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">Create Job</a>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Any status</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Job Type</label>
                        <select name="job_type" class="form-select">
                            <option value="">Any type</option>
                            @foreach($jobTypes as $value => $label)
                                <option value="{{ $value }}" @selected(request('job_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title, slug, location">
                    </div>
                    <div class="col-md-2 d-flex gap-2 align-items-center justify-content-start pt-3">
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered js-admin-datatable" data-datatable-order='[[0,"asc"]]' data-datatable-search-placeholder="Search jobs">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Job</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Vacancies</th>
                                <th>Expiry</th>
                                <th>Flags</th>
                                <th>Status</th>
                                <th class="text-end table-actions" data-dt-orderable="false" data-dt-searchable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobs as $job)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $job->title }}</div>
                                        <div class="text-muted small">/{{ $job->slug }}</div>
                                    </td>
                                    <td>{{ $job->category?->name ?? 'Uncategorized' }}</td>
                                    <td>{{ $job->location }}</td>
                                    <td>{{ $jobTypes[$job->job_type] ?? $job->job_type }}</td>
                                    <td>{{ $job->vacancies }}</td>
                                    <td>{{ $job->expiry_date?->format('d M Y') ?? 'No expiry' }}</td>
                                    <td>
                                        @if($job->is_featured)
                                            <span class="badge bg-warning">Featured</span>
                                        @endif
                                        @if($job->isExpired())
                                            <span class="badge bg-danger text-danger">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $job->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $statusOptions[$job->status] ?? ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end table-actions">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('careers.show', $job->slug) }}" target="_blank" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-outline" width="18" height="18"></iconify-icon></a>
                                            @if($adminUser?->can('careers.jobs.update'))
                                                <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('careers.jobs.delete'))
                                                <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job and its applications?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-line-duotone" width="18" height="18"></iconify-icon></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No jobs found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
