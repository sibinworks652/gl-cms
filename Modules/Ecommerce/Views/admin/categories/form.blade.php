@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.ecommerce.categories.update', $category) : route('admin.ecommerce.categories.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Category' : 'Create Category' }}</h4>
                    <p class="text-muted mb-0">Set up storefront category navigation and product grouping.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.ecommerce.categories.index') }}" class="btn btn-light">Back</a>
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Root</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order</label>
                        <input name="order" type="number" class="form-control" value="{{ old('order', $category->order) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Image</label>
                        <input name="image" type="file" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $category->status))>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
