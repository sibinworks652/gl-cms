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
                                    <label class="form-label">Page Mode</label>
                                    <select name="content_mode" id="page-content-mode" class="form-select @error('content_mode') is-invalid @enderror">
                                        @foreach(\Modules\Page\Models\Page::contentModes() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('content_mode', $page->content_mode ?? 'blade') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Choose whether this page should use an auto-generated Blade file, rich content, or a full HTML/Blade design.</div>
                                    @error('content_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Internal note about this page">{{ old('description', $page->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 d-none" id="page-content-editor-wrap">
                                    <label class="form-label">Page Content</label>
                                    <div class="border rounded">
                                        <div id="page-content-editor" style="height: 320px;"></div>
                                    </div>
                                    <textarea name="content" id="page-content-input" hidden class="d-none @error('content') is-invalid @enderror">{{ old('content', $page->content) }}</textarea>
                                    <div class="form-text">Use the rich editor for standard page sections and formatted content.</div>
                                    @error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 d-none" id="page-html-editor-wrap">
                                    <label class="form-label">HTML / Blade Design</label>
                                    <textarea id="page-html-editor" class="form-control @error('content') is-invalid @enderror" rows="14" spellcheck="false">{{ old('content', $page->content) }}</textarea>
                                    <div class="form-text">Write full HTML or Blade markup here. The page will render this design directly.</div>
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
                            <h5 class="card-title mb-0">Page Output</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2" id="page-output-copy">This file path will be generated when the page is saved:</p>
                            <div class="p-3 bg-light rounded border" id="page-output-preview-wrap">
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
    const contentModeInput = document.getElementById('page-content-mode');
    const outputCopy = document.getElementById('page-output-copy');
    const contentWrap = document.getElementById('page-content-editor-wrap');
    const htmlWrap = document.getElementById('page-html-editor-wrap');
    const htmlEditor = document.getElementById('page-html-editor');
    const hiddenContentInput = document.getElementById('page-content-input');
    let slugTouched = Boolean(slugInput?.value);
    let viewPathTouched = Boolean(viewPathInput?.value);
    const quill = new Quill('#page-content-editor', {
        theme: 'snow',
        placeholder: 'Write page content...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        }
    });

    quill.root.innerHTML = @json(old('content', $page->content ?? ''));

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

    const syncModeUi = function () {
        const mode = contentModeInput?.value || 'blade';
        if (contentWrap) {
            contentWrap.classList.toggle('d-none', mode !== 'content');
        }
        if (htmlWrap) {
            htmlWrap.classList.toggle('d-none', mode !== 'html');
        }
        if (viewPathInput) {
            viewPathInput.closest('.col-12')?.classList.toggle('opacity-75', mode !== 'blade');
        }
        if (outputCopy) {
            outputCopy.textContent = mode === 'blade'
                ? 'This file path will be generated when the page is saved:'
                : 'This page will render directly from the stored editor content:';
        }
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

    contentModeInput?.addEventListener('change', syncModeUi);

    titleInput?.addEventListener('input', function () {
        if (!slugTouched && slugInput) {
            slugInput.value = slugify(titleInput.value);
            if (!viewPathTouched && viewPathInput) {
                viewPathInput.value = 'pages.' + (slugInput.value || 'home');
            }
            updateFilePreview();
        }
    });

    document.getElementById('page-form')?.addEventListener('submit', function () {
        if (!hiddenContentInput) {
            return;
        }

        if ((contentModeInput?.value || 'blade') === 'content') {
            let html = quill.root.innerHTML;
            if (html === '<p><br></p>') {
                html = '';
            }
            hiddenContentInput.value = html;
            return;
        }

        if ((contentModeInput?.value || 'blade') === 'html') {
            hiddenContentInput.value = htmlEditor?.value || '';
            return;
        }

        hiddenContentInput.value = '';
    });

    updateFilePreview();
    syncModeUi();
});
</script>
@endpush
