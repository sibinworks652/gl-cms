@extends('admin.layouts.app')

@php
$selectedTags = old('tag_ids', $product->tags->pluck('id')->all());
$selectedAttributes = old('attribute_ids', $product->attributes->pluck('id')->all());
$variants = old('variants');
if ($variants === null) {
    $variants = $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'sku' => $variant->sku,
        'price' => $variant->price,
        'stock' => $variant->stock,
        'status' => $variant->status ? 1 : 0,
        'track_inventory' => $variant->track_inventory ? 1 : 0,
        'low_stock_threshold' => $variant->low_stock_threshold,
        'allow_backorder' => $variant->allow_backorder ? 1 : 0,
        'attribute_option_ids' => $variant->attributeOptions->pluck('id')->all(),
        'label' => $variant->attributeOptions->pluck('name')->all(),
        'size' => $variant->size,
        'color' => $variant->color,
    ])->values()->all();
}
if (empty($variants)) {
    $variants = [[
        'id' => '',
        'sku' => '',
        'price' => old('sale_price', $product->sale_price ?: $product->base_price),
        'stock' => 0,
        'status' => 1,
        'track_inventory' => 1,
        'low_stock_threshold' => old('low_stock_threshold', $product->low_stock_threshold ?? 10),
        'allow_backorder' => 0,
        'attribute_option_ids' => [],
        'label' => [],
        'size' => '',
        'color' => '',
    ]];
}
$attributeCatalog = $attributes->map(fn ($attribute) => [
    'id' => $attribute->id,
    'name' => $attribute->name,
    'type' => $attribute->type ?? 'select',
    'options' => $attribute->options->map(fn ($option) => [
        'id' => $option->id,
        'name' => $option->name,
        'value' => $option->value,
    ])->values()->all(),
])->values();
@endphp

@section('content')
<div class="container-xxl">
    <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('vendor.products.update', $product) : route('vendor.products.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">{{ $isEdit ? 'Edit Product' : 'Create Product' }}</h4>
                <p class="text-muted mb-0">Add product with variants, inventory, pricing, and more.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vendor.products.index') }}" class="btn btn-light">Back</a>
                <button class="btn btn-primary" type="submit">Save Product</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input id="baseSku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                                        {{ $category->parent ? $category->parent->name . ' / ' . $category->name : $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select">
                                <option value="">No Brand</option>
                                @if(isset($brands))
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base Price</label>
                            <input id="basePrice" name="base_price" type="number" step="0.01" class="form-control @error('base_price') is-invalid @enderror" value="{{ old('base_price', $product->base_price) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sale Price</label>
                            <input id="salePrice" name="sale_price" type="number" step="0.01" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base Stock</label>
                            <input name="stock" type="number" class="form-control" value="{{ old('stock', $product->stock) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" rows="2" class="form-control">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Attributes & Variants</h5></div>
                    <div class="card-body">
                        @if($attributes->isEmpty())
                            <p class="text-muted mb-0">No attributes available. You can add manual variants below.</p>
                        @else
                            <div class="row g-3">
                                @foreach($attributes as $attribute)
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input attribute-toggle" type="checkbox" name="attribute_ids[]" value="{{ $attribute->id }}" id="attr_{{ $attribute->id }}" data-attribute-id="{{ $attribute->id }}" @checked(in_array($attribute->id, $selectedAttributes))>
                                                <label class="form-check-label fw-semibold" for="attr_{{ $attribute->id }}">{{ $attribute->name }}</label>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($attribute->options as $option)
                                                    <button type="button" class="btn btn-sm {{ $attribute->type === 'color' ? 'btn-outline-dark' : 'btn-outline-secondary' }} option-chip" data-attribute-id="{{ $attribute->id }}" data-option-id="{{ $option->id }}" data-option-name="{{ $option->name }}" data-option-value="{{ $option->value }}">
                                                        @if($attribute->type === 'color')
                                                            <span class="d-inline-block rounded-circle me-1 align-middle" style="width:12px;height:12px;background:{{ $option->value ?: '#999999' }};"></span>
                                                        @endif
                                                        {{ $option->name }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="card-footer d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary btn-sm" id="generateVariantsBtn">Generate Variants</button>
                        <button type="button" class="btn btn-light btn-sm" id="addVariantBtn">Add Manual Variant</button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Variants</h5></div>
                    <div class="card-body">
                        <div id="variantsContainer"></div>
                        <p class="small text-muted mb-0 mt-3">Each variant can have its own SKU, price, and stock.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="common-template-sidebar-sticky">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Publishing</h5></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $product->status))>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" @checked(old('is_featured', $product->is_featured))>
                            <label class="form-check-label" for="featured">Featured</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Media</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Featured Image</label>
                            <input name="featured_image" type="file" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gallery Images</label>
                            <input name="gallery_images[]" type="file" class="form-control" multiple>
                        </div>
                        @if($product->featured_image_url)
                            <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3">
                        @endif
                        @if($isEdit && $product->images->isNotEmpty())
                            <div class="row g-2">
                                @foreach($product->images as $image)
                                    <div class="col-6"><img src="{{ $image->url }}" alt="" class="img-fluid rounded border"></div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if(isset($tags) && $tags->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Tags</h5></div>
                    <div class="card-body">
                        @foreach($tags as $tag)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="tag_ids[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags))>
                                <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Inventory</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input name="low_stock_threshold" type="number" class="form-control" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="track_inventory" value="0">
                            <input class="form-check-input" type="checkbox" name="track_inventory" value="1" id="trackInventory" @checked(old('track_inventory', $product->track_inventory ?? true))>
                            <label class="form-check-label" for="trackInventory">Track inventory</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="allow_backorder" value="0">
                            <input class="form-check-input" type="checkbox" name="allow_backorder" value="1" id="allowBackorder" @checked(old('allow_backorder', $product->allow_backorder ?? false))>
                            <label class="form-check-label" for="allowBackorder">Allow backorders</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Tax & Shipping</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tax Percentage (%)</label>
                            <input name="tax_percentage" type="number" step="0.01" class="form-control" value="{{ old('tax_percentage', $product->tax_percentage) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Weight (kg)</label>
                            <input name="shipping_weight" type="number" step="0.01" class="form-control" value="{{ old('shipping_weight', $product->shipping_weight) }}">
                        </div>
                        <div>
                            <label class="form-label">Shipping Cost</label>
                            <input name="shipping_cost" type="number" step="0.01" class="form-control" value="{{ old('shipping_cost', $product->shipping_cost) }}">
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
    const variants = @json($variants);
    const attributes = @json($attributeCatalog);
    const container = document.getElementById('variantsContainer');
    const baseSku = document.getElementById('baseSku');
    const salePrice = document.getElementById('salePrice');
    const basePrice = document.getElementById('basePrice');
    let index = 0;

    function valuePrice() { return salePrice.value || basePrice.value || ''; }
    function key(ids) { return ids.slice().sort((a, b) => a - b).join(':'); }
    function shortText(value) { return String(value || '').trim().replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-+|-+$/g, '').toUpperCase().slice(0, 8); }

    function rowHtml(i, variant) {
        const labels = Array.isArray(variant.label) ? variant.label.filter(Boolean) : [];
        const optionIds = Array.isArray(variant.attribute_option_ids) ? variant.attribute_option_ids : [];
        return `<div class="border rounded p-3 mb-3" data-variant-row>
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div><div class="fw-semibold">Variant</div><div class="small text-muted">${labels.join(' / ') || 'Manual variant'}</div></div>
                <button type="button" class="btn btn-sm btn-light text-danger remove-variant-btn">Remove</button>
            </div>
            <input type="hidden" name="variants[${i}][id]" value="${variant.id || ''}">
            ${optionIds.map(id => `<input type="hidden" name="variants[${i}][attribute_option_ids][]" value="${id}">`).join('')}
            ${labels.map(label => `<input type="hidden" name="variants[${i}][label][]" value="${label}">`).join('')}
            <div class="mb-3">${labels.map(label => `<span class="badge bg-light text-dark me-1">${label}</span>`).join('') || '<span class="text-muted small">No attribute linked.</span>'}</div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">SKU</label><input name="variants[${i}][sku]" class="form-control" value="${variant.sku || ''}" required></div>
                <div class="col-md-2"><label class="form-label">Price</label><input name="variants[${i}][price]" type="number" step="0.01" class="form-control" value="${variant.price ?? ''}"></div>
                <div class="col-md-2"><label class="form-label">Stock</label><input name="variants[${i}][stock]" type="number" class="form-control" value="${variant.stock ?? 0}"></div>
                <div class="col-md-2"><label class="form-label">Low</label><input name="variants[${i}][low_stock_threshold]" type="number" class="form-control" value="${variant.low_stock_threshold ?? 10}"></div>
                <div class="col-md-2"><label class="form-label">Status</label><select name="variants[${i}][status]" class="form-select"><option value="1" ${String(variant.status ?? 1) === '1' ? 'selected' : ''}>On</option><option value="0" ${String(variant.status ?? 1) === '0' ? 'selected' : ''}>Off</option></select></div>
            </div>
        </div>`;
    }

    function appendRow(variant) {
        container.insertAdjacentHTML('beforeend', rowHtml(index, variant));
        index += 1;
    }

    function loadRows() {
        container.innerHTML = '';
        index = 0;
        variants.forEach(appendRow);
    }

    function selectedCombinations() {
        const groups = Array.from(document.querySelectorAll('.attribute-toggle:checked')).map(input => {
            const attribute = attributes.find(item => Number(item.id) === Number(input.dataset.attributeId));
            const buttons = Array.from(document.querySelectorAll('.option-chip.is-selected[data-attribute-id="' + input.dataset.attributeId + '"]'));
            return {
                attribute: attribute,
                options: buttons.map(button => ({
                    attributeName: attribute ? attribute.name : 'Attribute',
                    attributeType: attribute ? attribute.type : 'select',
                    optionId: Number(button.dataset.optionId),
                    optionName: button.dataset.optionName
                }))
            };
        }).filter(group => group.options.length);
        if (!groups.length) return [];
        return groups.reduce((result, group) => result.flatMap(base => group.options.map(option => base.concat(option))), [[]]);
    }

    function buildVariant(combo) {
        const suffix = combo.map(item => shortText(item.attributeName).slice(0, 2) + shortText(item.optionName).slice(0, 2)).join('-');
        return {
            id: '',
            sku: suffix ? (baseSku.value + '-' + suffix) : baseSku.value,
            price: valuePrice(),
            stock: 0,
            status: 1,
            track_inventory: 1,
            low_stock_threshold: 10,
            allow_backorder: 0,
            attribute_option_ids: combo.map(item => item.optionId),
            label: combo.map(item => item.optionName)
        };
    }

    document.querySelectorAll('.option-chip').forEach(button => {
        button.addEventListener('click', function () {
            const toggle = document.querySelector('.attribute-toggle[data-attribute-id="' + button.dataset.attributeId + '"]');
            if (toggle) toggle.checked = true;
            button.classList.toggle('is-selected');
            button.classList.toggle('btn-primary');
            button.classList.toggle('btn-outline-secondary');
            button.classList.toggle('btn-outline-dark');
        });
    });

    document.getElementById('generateVariantsBtn').addEventListener('click', function () {
        const seen = Array.from(container.querySelectorAll('[data-variant-row]')).map(row => key(Array.from(row.querySelectorAll('input[name*="[attribute_option_ids]"]')).map(input => Number(input.value)).filter(Boolean)));
        selectedCombinations().forEach(combo => {
            const variant = buildVariant(combo);
            const comboKey = key(variant.attribute_option_ids);
            if (!seen.includes(comboKey)) {
                appendRow(variant);
                seen.push(comboKey);
            }
        });
    });

    document.getElementById('addVariantBtn').addEventListener('click', function () {
        appendRow({id:'', sku:baseSku.value || '', price:valuePrice(), stock:0, status:1, track_inventory:1, low_stock_threshold:10, allow_backorder:0, attribute_option_ids:[], label:[]});
    });

    container.addEventListener('click', function (event) {
        if (event.target.closest('.remove-variant-btn') && container.querySelectorAll('[data-variant-row]').length > 1) {
            event.target.closest('[data-variant-row]').remove();
        }
    });

    loadRows();
});
</script>
@endpush