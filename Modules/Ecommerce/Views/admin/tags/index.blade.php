@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Tags</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.tags.create') }}" class="btn btn-primary">
                        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Add Tag
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tags as $tag)
                            <tr>
                                <td><strong>{{ $tag->name }}</strong></td>
                                <td>{{ $tag->slug }}</td>
                                <td>{{ $tag->type ?? 'general' }}</td>
                                <td>{{ $tag->products()->count() }}</td>
                                <td>
                                    <span class="badge bg-{{ $tag->status ? 'success' : 'secondary' }}">
                                        {{ $tag->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.ecommerce.tags.edit', $tag) }}" class="btn btn-soft-warning btn-sm">
                                     <iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon>
                                    </a>
                                    <form method="POST" action="{{ route('admin.ecommerce.tags.destroy', $tag) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"  class="btn btn-sm btn-soft-danger" >
                                            <iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No tags found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
