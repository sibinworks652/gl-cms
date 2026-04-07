@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Form Builder</h4>
                            <p class="text-muted mb-0">Build dynamic forms, save schema as JSON, and render them on the frontend.</p>
                        </div>
                        @if($adminUser?->can('forms.create'))
                            <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-sm">Create Form</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Fields</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($forms as $form)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $form->name }}</div>
                                                <div class="text-muted small">{{ $form->slug }}</div>
                                            </td>
                                            <td>{{ count($form->schema ?? []) }}</td>
                                            <td>
                                                <span class="badge {{ $form->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $form->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    @if($adminUser?->can('forms.view'))
                                                        <a href="{{ route('admin.forms.view', $form) }}" class="btn btn-soft-success btn-sm"><iconify-icon icon="solar:eye-broken" width="20" height="20" /></a>
                                                    @endif
                                                    @if($adminUser?->can('forms.update'))
                                                        <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                                                    @endif
                                                    @if($adminUser?->can('forms.delete'))
                                                        <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" onsubmit="return confirm('Delete this form?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">No forms created yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $forms->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
