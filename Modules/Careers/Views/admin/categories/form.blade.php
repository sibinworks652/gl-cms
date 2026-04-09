@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.job-categories.update', $category) : route('admin.job-categories.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Job Category' : 'Create Job Category' }}</h4>
                    <p class="text-muted mb-0">Use categories like Development, Marketing, Design, and HR.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.job-categories.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="career-category-name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="career-category-slug" value="{{ old('slug', $category->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="development">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="career-category-active" @checked(old('is_active', $category->is_active ?? true) == 1)>
                                <label class="form-check-label" for="career-category-active">Active category</label>
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
    const nameInput = document.getElementById('career-category-name');
    const slugInput = document.getElementById('career-category-slug');
    let slugTouched = Boolean(slugInput && slugInput.value);

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            slugTouched = true;
        });
    }

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            if (!slugTouched) {
                slugInput.value = nameInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }
});
</script>
@endpush
