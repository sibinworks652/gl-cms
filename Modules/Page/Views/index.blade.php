@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Pages</h4>
                <p class="text-muted mb-0">Manage frontend pages and the Blade views they render.</p>
            </div>
            @if($adminUser?->can('pages.create'))
                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">Create Page</a>
            @endif
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th>Slug</th>
                                <th>Blade Path</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pages as $page)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $page->title }}</div>
                                        @if($page->description)
                                            <div class="text-muted small">{{ $page->description }}</div>
                                        @endif
                                    </td>
                                    <td>/{{ $page->slug }}</td>
                                    <td><code>{{ $page->view_path }}</code></td>
                                    <td>
                                        <span class="badge {{ $page->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $page->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('pages.update'))
                                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('pages.delete'))
                                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page record? The Blade file will not be removed automatically.');">
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
                                    <td colspan="5" class="text-center py-5 text-muted">No pages found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pages->hasPages())
                <div class="card-footer">
                    {{ $pages->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
