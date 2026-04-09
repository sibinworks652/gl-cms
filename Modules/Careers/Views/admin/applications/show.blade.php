@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Application #{{ $application->id }}</h4>
                <p class="text-muted mb-0">Review candidate details, cover letter, and resume.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-light">Back</a>
                @if($application->resume_path)
                    <a href="{{ route('admin.applications.resume.download', $application) }}" class="btn btn-primary">Download Resume</a>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Applicant</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{ $application->name }}</dd>
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $application->email }}</dd>
                            <dt class="col-sm-4">Phone</dt>
                            <dd class="col-sm-8">{{ $application->phone }}</dd>
                            <dt class="col-sm-4">LinkedIn</dt>
                            <dd class="col-sm-8">
                                @if($application->linkedin_url)
                                    <a href="{{ $application->linkedin_url }}" target="_blank">{{ $application->linkedin_url }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                            <dt class="col-sm-4">Applied</dt>
                            <dd class="col-sm-8">{{ $application->created_at?->format('d M Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Job</h5>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ $application->job?->title }}</div>
                        <div class="text-muted small mb-3">{{ $application->job?->category?->name ?? 'Uncategorized' }}</div>
                        <div class="mb-2"><strong>Location:</strong> {{ $application->job?->location }}</div>
                        <div class="mb-2"><strong>Type:</strong> {{ \Modules\Careers\Models\Job::jobTypes()[$application->job?->job_type] ?? $application->job?->job_type }}</div>
                        <div><strong>Experience:</strong> {{ $application->job?->experience }}</div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.applications.status.update', $application) }}">
                            @csrf
                            @method('PATCH')
                            <label class="form-label">Application Status</label>
                            <select name="status" class="form-select">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary mt-3">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Cover Letter</h5>
                    </div>
                    <div class="card-body">
                        @if($application->cover_letter)
                            <div class="text-muted" style="white-space: pre-line;">{{ $application->cover_letter }}</div>
                        @else
                            <div class="text-muted">No cover letter provided.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
