@extends('admin.layouts.app')

@section('content')
<div class="container-xxl">
    <form method="POST" action="{{ $isEdit ? route('admin.ecommerce.coupons.update', $coupon) : route('admin.ecommerce.coupons.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $isEdit ? 'Edit Coupon' : 'Create Coupon' }}</h4>
                <p class="text-muted mb-0">Create coupon codes customers can apply at checkout.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ecommerce.coupons.index') }}" class="btn btn-light">Back</a>
                <button class="btn btn-primary" type="submit">Save Coupon</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $coupon->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Coupon Code</label><input name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required></div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="percentage" @selected(old('type', $coupon->type) === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Value</label><input name="value" type="number" step="0.01" class="form-control" value="{{ old('value', $coupon->value) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Usage Limit</label><input name="usage_limit" type="number" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}"></div>
                        <div class="col-md-6"><label class="form-label">Start Date</label><input name="start_date" type="datetime-local" class="form-control" value="{{ old('start_date', $coupon->start_date?->format('Y-m-d\TH:i')) }}"></div>
                        <div class="col-md-6"><label class="form-label">End Date</label><input name="end_date" type="datetime-local" class="form-control" value="{{ old('end_date', $coupon->end_date?->format('Y-m-d\TH:i')) }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $coupon->status))>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
