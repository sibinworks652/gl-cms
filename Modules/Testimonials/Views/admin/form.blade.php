@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="commons-ticky-template-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Testimonial' : 'Create Testimonial' }}</h4>
                    <p class="text-muted mb-0">Add social proof with client details, review content, ratings, and display settings.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Save Testimonial</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Client Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" id="testimonial-name" class="form-control @error('name') error-input-bottom @enderror" value="{{ old('name', $testimonial->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" id="testimonial-slug" class="form-control @error('slug') error-input-bottom @enderror" value="{{ old('slug', $testimonial->slug) }}" placeholder="john-doe-review">
                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="company" class="form-control @error('company') error-input-bottom @enderror" value="{{ old('company', $testimonial->company) }}">
                                    @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control @error('designation') error-input-bottom @enderror" value="{{ old('designation', $testimonial->designation) }}">
                                    @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control @error('location') error-input-bottom @enderror" value="{{ old('location', $testimonial->location) }}">
                                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Project / Service</label>
                                    <input type="text" name="project_name" class="form-control @error('project_name') error-input-bottom @enderror" value="{{ old('project_name', $testimonial->project_name) }}">
                                    @error('project_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Content</label>
                                    <textarea name="content" rows="7" class="form-control @error('content') error-input-bottom @enderror" required>{{ old('content', $testimonial->content) }}</textarea>
                                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                    <input type="text" name="meta_title" class="form-control @error('meta_title') error-input-bottom @enderror" value="{{ old('meta_title', $testimonial->meta_title) }}">
                                    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" rows="4" class="form-control @error('meta_description') error-input-bottom @enderror">{{ old('meta_description', $testimonial->meta_description) }}</textarea>
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
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select @error('rating') error-input-bottom @enderror">
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected((int) old('rating', $testimonial->rating) === $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                                @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" name="order" class="form-control @error('order') error-input-bottom @enderror" value="{{ old('order', $testimonial->order) }}">
                                @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $testimonial->status) == 1)>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is-featured" @checked(old('is_featured', $testimonial->is_featured) == 1)>
                                <label class="form-check-label" for="is-featured">Featured</label>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Client Image</h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control @error('image') error-input-bottom @enderror" accept="image/*">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($testimonial->image_url)
                                <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="img-fluid rounded mt-3">
                            @endif
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
    const nameInput = document.getElementById('testimonial-name');
    const slugInput = document.getElementById('testimonial-slug');
    let slugTouched = Boolean(slugInput && slugInput.value);

    slugInput?.addEventListener('input', () => slugTouched = true);
    nameInput?.addEventListener('input', function () {
        if (!slugTouched) {
            slugInput.value = nameInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });
});
</script>
@endpush
