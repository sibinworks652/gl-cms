@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Applications</h4>
                <p class="text-muted mb-0">Track candidates, resumes, and hiring decisions from one workflow.</p>
            </div>
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Back to Jobs</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Job</label>
                        <select name="job" class="form-select">
                            <option value="">All jobs</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" @selected((string) request('job') === (string) $job->id)>{{ $job->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Any status</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Applicant name, email, phone">
                    </div>
                    <div class="col-md-3 d-flex gap-2 align-items-center justify-content-start pt-3">
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered js-admin-datatable" data-datatable-order='[[4,"desc"]]' data-datatable-search-placeholder="Search applications">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Applicant</th>
                                <th>Job</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Applied</th>
                                <th>Resume</th>
                                <th class="text-end table-actions" data-dt-orderable="false" data-dt-searchable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $application->name }}</div>
                                        <div class="text-muted small">{{ $application->linkedin_url ?: 'No LinkedIn URL' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $application->job?->title }}</div>
                                        <div class="text-muted small">{{ $application->job?->category?->name ?? 'Uncategorized' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $application->email }}</div>
                                        <div class="text-muted small">{{ $application->phone }}</div>
                                    </td>
                                    <td>
                                        @if($adminUser?->can('careers.applications.update'))
                                            <form method="POST" action="{{ route('admin.applications.status.update', $application) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    @foreach($statusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $statusOptions[$application->status] ?? ucfirst($application->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $application->created_at?->format('d M Y h:i A') }}</td>
                                    <td>
                                        @if($application->resume_path)
                                            <a href="{{ route('admin.applications.resume.download', $application) }}" class="btn btn-soft-success btn-sm"><iconify-icon icon="solar:download-minimalistic-broken" width="18" height="18"></iconify-icon></a>
                                        @else
                                            <span class="text-muted">No resume</span>
                                        @endif
                                    </td>
                                    <td class="text-end table-actions">
                                        <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-outline" width="18" height="18"></iconify-icon></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No applications found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
