@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-xl-11">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">{{ $form->name }} Submissions</h4>
                            <p class="text-muted mb-0">Stored frontend form entries saved as JSON payloads.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.forms.view', $form) }}" class="btn btn-light btn-sm">Back to Form</a>
                            <a href="{{ route('forms.public.show', $form->slug) }}" class="btn btn-primary btn-sm" target="_blank">Open Frontend Form</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($submissions as $submission)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                                    <div>
                                        <div class="fw-semibold">Submission #{{ $submission->id }}</div>
                                        <div class="text-muted small">
                                            {{ optional($submission->submitted_at)->format('d M Y h:i A') ?: '-' }}
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        IP: {{ $submission->ip_address ?: '-' }}
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(($submission->payload ?? []) as $item)
                                                <tr>
                                                    <td>{{ $item['label'] ?? ($item['name'] ?? '-') }}</td>
                                                    <td>
                                                        @php($value = $item['value'] ?? null)
                                                        @if(is_array($value))
                                                            {{ implode(', ', $value) ?: '-' }}
                                                        @else
                                                            {{ $value !== null && $value !== '' ? $value : '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">No submissions stored for this form yet.</div>
                        @endforelse
                    </div>
                    <div class="card-footer">
                        {{ $submissions->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
