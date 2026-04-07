@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
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
                                        <div class="border-bottom p-2 d-flex gap-2">
                                            <button type="button" class="btn btn-light btn-sm rich-command" data-command="bold">Bold</button>
                                            <button type="button" class="btn btn-light btn-sm rich-command" data-command="italic">Italic</button>
                                            <button type="button" class="btn btn-light btn-sm rich-command" data-command="insertUnorderedList">List</button>
                                        </div>
                                        <div id="service-description-editor" class="p-3" contenteditable="true" style="min-height:240px;">{!! old('full_description', $service->full_description) !!}</div>
                                    </div>
                                    <textarea name="full_description" id="service-description-input" class="d-none @error('full_description') is-invalid @enderror">{{ old('full_description', $service->full_description) }}</textarea>
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
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Publishing</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror">
                                    <option value="">No category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('service_category_id', $service->service_category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $service->sort_order) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch mb-2">
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
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('service-title');
    const slugInput = document.getElementById('service-slug');
    const editor = document.getElementById('service-description-editor');
    const descriptionInput = document.getElementById('service-description-input');
    const form = editor ? editor.closest('form') : null;
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

    document.querySelectorAll('.rich-command').forEach((button) => {
        button.addEventListener('click', function () {
            document.execCommand(button.dataset.command, false, null);
            editor?.focus();
        });
    });

    if (form && editor && descriptionInput) {
        form.addEventListener('submit', function () {
            descriptionInput.value = editor.innerHTML;
        });
    }
});
</script>
@endpush
