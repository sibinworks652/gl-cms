@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Banner Slide' : 'Create Banner Slide' }}</h4>
                        <p class="text-muted mb-0">Create hero banners with media, content, button, schedule, and status controls.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.banners.update', $slide) : route('admin.banners.store') }}">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $slide->title) }}">
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $slide->subtitle) }}">
                                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $slide->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Media Type</label>
                                    <select name="media_type" id="banner-media-type" class="form-select @error('media_type') is-invalid @enderror">
                                        @foreach ($mediaTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('media_type', $slide->media_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('media_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 mb-3" id="banner-image-field">
                                    <label class="form-label">{{ $isEdit ? 'Replace Image' : 'Banner Image' }}</label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if ($slide->image_path)
                                        <div class="mt-2"><img src="{{ asset('storage/' . $slide->image_path) }}" alt="{{ $slide->title }}" class="img-fluid rounded" style="max-height: 160px;"></div>
                                    @endif
                                </div>
                                <div class="col-md-8 mb-3" id="banner-video-field">
                                    <label class="form-label">Video URL</label>
                                    <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $slide->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                                    @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Button Label</label>
                                    <input type="text" name="button_label" class="form-control @error('button_label') is-invalid @enderror" value="{{ old('button_label', $slide->button_label) }}">
                                    @error('button_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Link Type</label>
                                    <select name="button_link_type" id="banner-link-type" class="form-select @error('button_link_type') is-invalid @enderror">
                                        @foreach ($linkTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('button_link_type', $slide->button_link_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('button_link_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" id="banner-link-label">Button Link</label>
                                    <input type="text" name="button_link" id="banner-link-input" class="form-control @error('button_link') is-invalid @enderror" value="{{ old('button_link', $slide->button_link) }}" list="banner-page-suggestions">
                                    <datalist id="banner-page-suggestions">
                                        @foreach ($pageSuggestions as $suggestion)
                                            <option value="{{ $suggestion }}"></option>
                                        @endforeach
                                    </datalist>
                                    @error('button_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', $slide->starts_at?->format('Y-m-d\TH:i')) }}">
                                    <small class="text-muted d-block mt-1">The banner starts showing from this date and time. Leave blank to show it immediately.</small>
                                    @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', $slide->ends_at?->format('Y-m-d\TH:i')) }}">
                                    <small class="text-muted d-block mt-1">The banner stops showing after this date and time. Leave blank to keep it visible with no expiry.</small>
                                    @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <div class="w-100">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="open_in_new_tab" value="1" id="banner-open-tab" {{ old('open_in_new_tab', $slide->open_in_new_tab) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="banner-open-tab">Open button link in new tab</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="banner-is-active" {{ old('is_active', $slide->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="banner-is-active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Slide' : 'Create Slide' }}</button>
                                <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const mediaType = document.getElementById('banner-media-type');
    const imageField = document.getElementById('banner-image-field');
    const videoField = document.getElementById('banner-video-field');
    const linkType = document.getElementById('banner-link-type');
    const linkInput = document.getElementById('banner-link-input');
    const linkLabel = document.getElementById('banner-link-label');

    function updateMediaFields() {
        const isImage = mediaType.value === 'image';
        imageField.style.display = isImage ? '' : 'none';
        videoField.style.display = isImage ? 'none' : '';
    }

    function updateLinkField() {
        const isPage = linkType.value === 'page';
        linkLabel.textContent = isPage ? 'Internal Page / Route' : 'External URL';
        if (isPage) {
            linkInput.setAttribute('list', 'banner-page-suggestions');
            linkInput.placeholder = '/ or menu-preview';
        } else {
            linkInput.removeAttribute('list');
            linkInput.placeholder = 'https://example.com';
        }
    }

    mediaType.addEventListener('change', updateMediaFields);
    linkType.addEventListener('change', updateLinkField);
    updateMediaFields();
    updateLinkField();
})();
</script>
@endpush
