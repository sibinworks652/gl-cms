@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}" id="serviceForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex commons-ticky-template-toolbar justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Service' : 'Create Service' }}</h4>
                    <p class="text-muted mb-0">Build a service page with media, SEO, category, and display controls.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Save Service</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Service Content</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" id="service-title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $service->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" id="service-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $service->slug) }}" placeholder="web-development">
                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $service->short_description) }}</textarea>
                                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Description</label>
                                    <div class="border rounded">
                                        <div id="editor" style="height: 300px;"></div>
                                    </div>
                                    <textarea name="full_description" id="content" hidden class="d-none @error('full_description') is-invalid @enderror">{{ old('full_description', $service->full_description) }}</textarea>
                                    @error('full_description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">SEO</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $service->meta_title) }}">
                                    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" rows="3" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $service->meta_description) }}</textarea>
                                    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="common-template-sidebar-sticky">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Publishing</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="service_category_id" id="service-category-select" class="form-select @error('service_category_id') is-invalid @enderror">
                                    <option value="">No category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('service_category_id', $service->service_category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($adminUser?->can('service-categories.create'))
                                    <div
                                        class="input-group mt-2"
                                        id="service-category-inline-create"
                                        data-store-url="{{ route('admin.service-categories.store') }}"
                                        data-csrf-token="{{ csrf_token() }}"
                                    >
                                        <input type="text" class="form-control" id="new-service-category-name" placeholder="New category name" aria-label="New category name">
                                        <button class="btn btn-outline-input" type="button" id="add-service-category-button">
                                            <span class="spinner-border spinner-border-sm me-1 d-none" id="add-service-category-spinner" aria-hidden="true"></span>
                                            <span id="add-service-category-label">Add</span>
                                        </button>
                                    </div>
                                    <div class="small mt-1" id="service-category-inline-status" aria-live="polite"></div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $service->sort_order) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active" @checked(old('is_active', $service->is_active) == 1)>
                                <label class="form-check-label" for="is-active">Active</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is-featured" @checked(old('is_featured', $service->is_featured) == 1)>
                                <label class="form-check-label" for="is-featured">Featured</label>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Media & CTA</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Icon</label>
                                <textarea name="icon" rows="3" class="form-control @error('icon') is-invalid @enderror" placeholder="solar:code-square-bold-duotone or fa fa-code or SVG">{{ old('icon', $service->icon) }}</textarea>
                                <div class="form-text">Use an Iconify name, CSS icon class, or trusted SVG markup.</div>
                                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}" class="img-fluid rounded mt-2">
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CTA Label</label>
                                <input type="text" name="cta_label" class="form-control @error('cta_label') is-invalid @enderror" value="{{ old('cta_label', $service->cta_label) }}" placeholder="Get Quote">
                                @error('cta_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">CTA URL</label>
                                <input type="text" name="cta_url" class="form-control @error('cta_url') is-invalid @enderror" value="{{ old('cta_url', $service->cta_url) }}" placeholder="/contact">
                                @error('cta_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
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
    const titleInput = document.getElementById('service-title');
    const slugInput = document.getElementById('service-slug');
    let slugTouched = Boolean(slugInput && slugInput.value);

    if (slugInput) {
        slugInput.addEventListener('input', () => slugTouched = true);
    }

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugTouched) {
                slugInput.value = titleInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }

    const categoryInlineCreate = document.getElementById('service-category-inline-create');
    const categorySelect = document.getElementById('service-category-select');
    const categoryNameInput = document.getElementById('new-service-category-name');
    const addCategoryButton = document.getElementById('add-service-category-button');
    const addCategorySpinner = document.getElementById('add-service-category-spinner');
    const addCategoryLabel = document.getElementById('add-service-category-label');
    const categoryStatus = document.getElementById('service-category-inline-status');

    if (categoryInlineCreate && categorySelect && categoryNameInput && addCategoryButton) {
        const setCategoryLoading = function (isLoading) {
            categoryNameInput.disabled = isLoading;
            addCategoryButton.disabled = isLoading;
            addCategorySpinner?.classList.toggle('d-none', !isLoading);
            if (addCategoryLabel) {
                addCategoryLabel.textContent = isLoading ? 'Adding...' : 'Add';
            }
        };

        const setCategoryStatus = function (message, className) {
            if (!categoryStatus) {
                return;
            }

            categoryStatus.textContent = message;
            categoryStatus.className = 'small mt-1 ' + className;
        };

        const addCategory = async function () {
            const name = categoryNameInput.value.trim();

            if (!name) {
                categoryNameInput.focus();
                setCategoryStatus('Enter a category name first.', 'text-danger');
                return;
            }

            setCategoryLoading(true);
            setCategoryStatus('Saving category...', 'text-muted');

            const previousCategoryValue = categorySelect.value;
            const loadingOption = new Option('Adding category...', '__loading_category__', true, true);
            loadingOption.disabled = true;
            categorySelect.appendChild(loadingOption);

            try {
                const response = await fetch(categoryInlineCreate.dataset.storeUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': categoryInlineCreate.dataset.csrfToken || '',
                    },
                    body: JSON.stringify({
                        name: name,
                        is_active: 1,
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = result.errors?.name?.[0];
                    throw new Error(validationMessage || result.message || 'Unable to add category.');
                }

                loadingOption.remove();

                const category = result.category;
                const option = new Option(category.name, category.id, true, true);
                categorySelect.appendChild(option);
                categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                categoryNameInput.value = '';
                setCategoryStatus(result.message || 'Category added.', 'text-success');
            } catch (error) {
                loadingOption.remove();
                categorySelect.value = previousCategoryValue;
                setCategoryStatus(error.message || 'Unable to add category.', 'text-danger');
            } finally {
                setCategoryLoading(false);
                categoryNameInput.focus();
            }
        };

        addCategoryButton.addEventListener('click', addCategory);
        categoryNameInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addCategory();
            }
        });
    }
});
</script>
<script>
var quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Write something...',
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

// Load existing content safely
quill.root.innerHTML = @json(old('full_description', $service->full_description ?? ''));

// Sync on submit
document.getElementById('serviceForm').addEventListener('submit', function () {
    let html = quill.root.innerHTML;

    if (html === '<p><br></p>') {
        html = '';
    }

    document.getElementById('content').value = html;
});
</script>
@endpush
