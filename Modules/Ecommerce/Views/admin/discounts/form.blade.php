@extends('admin.layouts.app')

@section('content')
<div class="container-xxl">
    <form method="POST" action="{{ $isEdit ? route('admin.ecommerce.discounts.update', $discount) : route('admin.ecommerce.discounts.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $isEdit ? 'Edit Discount' : 'Create Discount' }}</h4>
                <p class="text-muted mb-0">Configure percentage or fixed offers for products and categories.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ecommerce.discounts.index') }}" class="btn btn-light">Back</a>
                <button class="btn btn-primary" type="submit">Save Discount</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $discount->name) }}" required></div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="percentage" @selected(old('type', $discount->type) === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old('type', $discount->type) === 'fixed')>Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Value</label><input name="value" type="number" step="0.01" class="form-control" value="{{ old('value', $discount->value) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Start Date</label><input name="start_date" type="datetime-local" class="form-control" value="{{ old('start_date', $discount->start_date?->format('Y-m-d\TH:i')) }}"></div>
                        <div class="col-md-4"><label class="form-label">End Date</label><input name="end_date" type="datetime-local" class="form-control" value="{{ old('end_date', $discount->end_date?->format('Y-m-d\TH:i')) }}"></div>
                        <div class="col-md-4"><label class="form-label">Usage Limit</label><input name="usage_limit" type="number" class="form-control" value="{{ old('usage_limit', $discount->usage_limit) }}"></div>
                        <div class="col-md-6">
                            <label class="form-label">Products</label>
                            <select name="product_ids[]" class="form-select" multiple size="8" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="choices-multiple-remove-button">
                                @php($selectedProducts = old('product_ids', $discount->products->pluck('id')->all()))
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProducts))>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categories</label>
                            <select name="category_ids[]" class="form-select" multiple size="8" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="choices-multiple-remove-button">
                                @php($selectedCategories = old('category_ids', $discount->categories->pluck('id')->all()))
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories))>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $discount->status))>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
