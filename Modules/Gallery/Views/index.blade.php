@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Gallery Albums</h4>
                            <p class="text-muted mb-0">Create albums and upload multiple images.</p>
                        </div>
                        @if($adminUser?->can('gallery.create'))
                            <a href="{{ route('admin.gallery.create') }}" class="btn btn-primary btn-sm">Create Album</a>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @forelse ($albums as $album)
                                <div class="col-xl-6">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="mb-1">{{ $album->title }}</h5>
                                                    <p class="text-muted mb-1">{{ $album->images_count }} images</p>
                                                    <span class="badge {{ $album->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                        {{ $album->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    @if($adminUser?->can('gallery.update'))
                                                        <a href="{{ route('admin.gallery.edit', $album) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="20" height="20" /></a>
                                                    @endif
                                                    @if($adminUser?->can('gallery.delete'))
                                                        <form method="POST" action="{{ route('admin.gallery.destroy', $album) }}" data-confirm="Delete this album and all images?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($album->description)
                                                <p class="text-muted">{{ $album->description }}</p>
                                            @endif

                                            <div class="row g-2">
                                                @forelse ($album->images as $image)
                                                    <div class="col-3">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->original_name }}" class="img-fluid rounded" style="height: 90px; width: 100%; object-fit: cover;">
                                                    </div>
                                                @empty
                                                    <div class="col-12">
                                                        <p class="text-muted mb-0">No images uploaded.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 text-muted">No gallery albums found.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card-footer">
                        {{ $albums->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
