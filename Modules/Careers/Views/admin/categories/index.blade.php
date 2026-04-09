@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Job Categories</h4>
                <p class="text-muted mb-0">Organize openings by department or hiring stream.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Jobs</a>
                @if($adminUser?->can('careers.categories.create'))
                    <a href="{{ route('admin.job-categories.create') }}" class="btn btn-primary">Create Category</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered js-admin-datatable" data-datatable-order='[[0,"asc"]]' data-datatable-search-placeholder="Search categories">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Category</th>
                                <th>Slug</th>
                                <th>Jobs</th>
                                <th>Status</th>
                                <th class="text-end table-actions" data-dt-orderable="false" data-dt-searchable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $category->name }}</div>
                                        <div class="text-muted small">{{ $category->description ?: 'No description' }}</div>
                                    </td>
                                    <td>{{ $category->slug }}</td>
                                    <td>{{ $category->jobs_count }}</td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end table-actions">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('careers.categories.update'))
                                                <a href="{{ route('admin.job-categories.edit', $category) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('careers.categories.delete'))
                                                <form method="POST" action="{{ route('admin.job-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Jobs will remain uncategorized.');">
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
                                    <td colspan="5" class="text-center py-5 text-muted">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
