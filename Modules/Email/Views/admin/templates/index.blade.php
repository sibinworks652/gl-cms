@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        @include('email::admin.partials.tabs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Email Templates</h4>
                    <p class="text-muted mb-0">Manage reusable templates for contact, invoice, welcome, notification, and system emails.</p>
                </div>
                @if($adminUser?->can('email.templates.create'))
                    <a href="{{ route('admin.email.templates.create') }}" class="btn btn-primary btn-sm">Create Template</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Variables</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $template->name }}</div>
                                        <div class="text-muted small">{{ $template->slug }}</div>
                                    </td>
                                    <td>{{ $template->subject }}</td>
                                    <td>
                                        @foreach(($template->variables ?? []) as $variable)
                                            <span class="badge bg-light text-dark me-1">&#123;{{ $variable }}&#125;</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge {{ $template->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $template->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.email.templates.preview', $template) }}" target="_blank" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-outline" width="20" height="20" /></a>
                                            @if($adminUser?->can('email.templates.update'))
                                                <a href="{{ route('admin.email.templates.edit', $template) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-linear" width="20" height="20" /></a>
                                            @endif
                                            @if($adminUser?->can('email.templates.delete'))
                                                <form method="POST" action="{{ route('admin.email.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-broken" width="20" height="20" /></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No email templates found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $templates->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection
