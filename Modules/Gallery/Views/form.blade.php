@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Album' : 'Create Album' }}</h4>
                        <p class="text-muted mb-0">Upload multiple images and organize them by album.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.gallery.update', $album) : route('admin.gallery.store') }}">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Album Title</label>
                                    <input type="text" name="title" class="form-control @error('title') error-input-bottom @enderror" value="{{ old('title', $album->title) }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="gallery-is-active" {{ old('is_active', $album->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="gallery-is-active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') error-input-bottom @enderror">{{ old('description', $album->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">{{ $isEdit ? 'Add More Images' : 'Images' }}</label>
                                <input type="file" name="images[]" class="form-control @error('images') error-input-bottom @enderror @error('images.*') error-input-bottom @enderror" multiple accept="image/*">
                                <small class="text-muted">You can select multiple images at once.</small>
                                @error('images')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('images.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($isEdit && $album->relationLoaded('images') && $album->images->isNotEmpty())
                                <div class="mb-4">
                                    <label class="form-label d-block">Existing Images</label>
                                    <div class="row g-3">
                                        @foreach ($album->images as $image)
                                            <div class="col-md-3">
                                                <div class="border rounded p-2">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->original_name }}" class="img-fluid rounded mb-2" style="height: 140px; width: 100%; object-fit: cover;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="delete-image-{{ $image->id }}">
                                                        <label class="form-check-label" for="delete-image-{{ $image->id }}">Delete</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Album' : 'Create Album' }}</button>
                                <a href="{{ route('admin.gallery.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
