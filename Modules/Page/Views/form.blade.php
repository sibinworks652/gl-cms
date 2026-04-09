@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}" id="page-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Page' : 'Create Page' }}</h4>
                    <p class="text-muted mb-0">Create a page record and automatically generate its Blade file from the view path.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Page' : 'Create Page' }}</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Page Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" id="page-title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" id="page-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug) }}" placeholder="about-us" required>
                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Blade View Path</label>
                                    <input type="text" name="view_path" id="page-view-path" class="form-control @error('view_path') is-invalid @enderror" value="{{ old('view_path', $page->view_path ?: ($page->slug ? 'pages.' . $page->slug : 'pages.home')) }}" placeholder="pages.about-us" required>
                                    <div class="form-text">Use dot notation or folders. Example: <code>pages.about-us</code> or <code>pages/company/about</code>. The Blade file will be created automatically if it does not exist.</div>
                                    @error('view_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Internal note about this page">{{ old('description', $page->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="page-is-active" @checked(old('is_active', $page->is_active ?? true))>
                                        <label class="form-check-label" for="page-is-active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Generated File</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">This file path will be generated when the page is saved:</p>
                            <div class="p-3 bg-light rounded border">
                                <code id="page-file-preview">{{ resource_path('views/' . str_replace('.', '/', old('view_path', $page->view_path ?: ($page->slug ? 'pages.' . $page->slug : 'pages.home'))) . '.blade.php') }}</code>
                            </div>
                            @if($isEdit)
                                <div class="mt-3">
                                    <a href="{{ url($page->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm">Open Frontend Page</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('page-title');
    const slugInput = document.getElementById('page-slug');
    const viewPathInput = document.getElementById('page-view-path');
    const filePreview = document.getElementById('page-file-preview');
    let slugTouched = Boolean(slugInput?.value);
    let viewPathTouched = Boolean(viewPathInput?.value);

    const slugify = function (value) {
        return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    };

    const normalizeViewPath = function (value, slug) {
        const raw = (value || '').trim().replace(/[\\/]+/g, '.').replace(/\.{2,}/g, '.').replace(/^\.|\.$/g, '');
        return raw || ('pages.' + (slug || 'home'));
    };

    const updateFilePreview = function () {
        if (!filePreview || !viewPathInput) {
            return;
        }

        filePreview.textContent = @json(resource_path('views')) + '/' + normalizeViewPath(viewPathInput.value, slugInput?.value || '').replace(/\./g, '/') + '.blade.php';
    };

    slugInput?.addEventListener('input', function () {
        slugTouched = true;
        if (!viewPathTouched) {
            viewPathInput.value = 'pages.' + (slugInput.value || 'home');
        }
        updateFilePreview();
    });

    viewPathInput?.addEventListener('input', function () {
        viewPathTouched = true;
        updateFilePreview();
    });

    titleInput?.addEventListener('input', function () {
        if (!slugTouched && slugInput) {
            slugInput.value = slugify(titleInput.value);
            if (!viewPathTouched && viewPathInput) {
                viewPathInput.value = 'pages.' + (slugInput.value || 'home');
            }
            updateFilePreview();
        }
    });

    updateFilePreview();
});
</script>
@endpush
