@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $isEdit ? 'Edit' : 'Create' }} Attribute</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.attributes.index') }}" class="btn btn-primary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-1"></iconify-icon> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.ecommerce.attributes.update', $attribute) : route('admin.ecommerce.attributes.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attribute Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Attribute Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $attribute->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">e.g., Size, Color, Material</small>
                        </div>

                        <div class="mb-0">
                            <label for="type" class="form-label">Attribute Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                <option value="select" @selected(old('type', $attribute->type ?? 'select') === 'select')>Select Option</option>
                                <option value="color" @selected(old('type', $attribute->type ?? 'select') === 'color')>Color Swatch</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choose color when each option should carry a hex color code.</small>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Options</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="add-option-btn">
                            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Add Option
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="options-container">
                            @php
                                $oldOptions = old('options', $attribute?->options->pluck('name')->toArray() ?? ['Small', 'Medium', 'Large']);
                                $oldOptionValues = old('option_values', $attribute?->options->pluck('value')->toArray() ?? ['', '', '']);
                                $selectedType = old('type', $attribute->type ?? 'select');
                            @endphp
                            @foreach($oldOptions as $index => $optionName)
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="options[]"
                                               value="{{ $optionName }}" placeholder="Option name">
                                    </div>
                                    <div class="col-md-4 option-value-wrapper {{ $selectedType === 'color' ? '' : 'd-none' }}">
                                        <input type="color" class="form-control form-control-color w-100" name="option_values[]"
                                               value="{{ $oldOptionValues[$index] ?: '#000000' }}" title="Color code">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-option-btn">
                                            <iconify-icon icon="mdi:delete"></iconify-icon>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Add options like: S, M, L, XL or Red, Blue, Green</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="common-template-sidebar-sticky">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                   {{ old('status', $attribute->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">{{ $isEdit ? 'Update' : 'Create' }} Attribute</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addOptionBtn = document.getElementById('add-option-btn');
        const optionsContainer = document.getElementById('options-container');
        const typeSelect = document.getElementById('type');

        function syncOptionMode() {
            const isColor = typeSelect.value === 'color';
            optionsContainer.querySelectorAll('.option-value-wrapper').forEach(function(wrapper) {
                wrapper.classList.toggle('d-none', !isColor);
            });
        }

        addOptionBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 option-row';
            row.innerHTML = `
                <div class="col-md-6">
                    <input type="text" class="form-control" name="options[]" placeholder="Option name">
                </div>
                <div class="col-md-4 option-value-wrapper ${typeSelect.value === 'color' ? '' : 'd-none'}">
                    <input type="color" class="form-control form-control-color w-100" name="option_values[]" value="#000000" title="Color code">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-option-btn">
                        <iconify-icon icon="mdi:delete"></iconify-icon>
                    </button>
                </div>
            `;
            optionsContainer.appendChild(row);
        });

        optionsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-option-btn')) {
                const rows = optionsContainer.querySelectorAll('.option-row');
                if (rows.length > 1) {
                    e.target.closest('.option-row').remove();
                }
            }
        });

        typeSelect.addEventListener('change', syncOptionMode);
        syncOptionMode();
    });
</script>
@endpush
@endsection
