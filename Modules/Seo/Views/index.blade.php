@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">SEO Settings</h4>
                    <p class="text-muted mb-0">Manage meta tags, social sharing, canonical URLs, and indexing for pages.</p>
                </div>
                @if($adminUser?->can('seo.create'))
                    <a href="{{ route('admin.seo.create') }}" class="btn btn-primary btn-sm">Create SEO</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Page</th>
                                <th>Meta Title</th>
                                <th>Indexing</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seoSettings as $seo)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $seo->page_label ?: $seo->page_key }}</div>
                                        <div class="text-muted small">{{ $seo->page_type }} | {{ $seo->page_key }}</div>
                                    </td>
                                    <td>{{ $seo->seo_meta_title ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $seo->seo_indexing === 'index' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $seo->seo_indexing }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $seo->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $seo->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('seo.update'))
                                                <a href="{{ route('admin.seo.edit', $seo) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                                            @endif
                                            @if($adminUser?->can('seo.delete'))
                                                <form action="{{ route('admin.seo.destroy', $seo) }}" method="POST" data-confirm="Delete this SEO setting?">
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
                                    <td colspan="5" class="text-center py-4 text-muted">No SEO settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $seoSettings->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection
