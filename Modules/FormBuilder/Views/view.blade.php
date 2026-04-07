@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">{{ $form->name }}</h4>
                            <p class="text-muted mb-0">{{ $form->description ?: 'Dynamic form preview and schema overview.' }}</p>
                        </div>
                        <div class="d-flex gap-2 w-50">
                            <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-warning btn-sm w-50"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                            <a href="{{ route('admin.forms.submissions', $form) }}" class="btn btn-soft-success btn-sm w-50">Submissions</a>
                            <a href="{{ route('forms.public.show', $form->slug) }}" class="btn btn-light btn-sm w-50" target="_blank">Frontend View</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3"><strong>Slug:</strong> {{ $form->slug }}</div>
                        <div class="mb-3"><strong>Status:</strong> {{ $form->is_active ? 'Active' : 'Inactive' }}</div>
                        <div class="mb-3"><strong>Fields:</strong> {{ count($form->schema ?? []) }}</div>
                        <div class="mb-3"><strong>Submissions:</strong> {{ $form->submissions()->count() }}</div>
                        <pre class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ json_encode($form->schema ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                        @if($recentSubmissions->isNotEmpty())
                            <div class="mt-4">
                                <h5 class="mb-3">Recent Submissions</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Submitted</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentSubmissions as $submission)
                                                <tr>
                                                    <td>{{ $submission->id }}</td>
                                                    <td>{{ optional($submission->submitted_at)->format('d M Y h:i A') ?: '-' }}</td>
                                                    <td>{{ $submission->ip_address ?: '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
