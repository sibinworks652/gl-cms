@extends('ecommerce::web.layout')

@section('title', $product->name)

@php
$allImages = collect([$product->featured_image_url])->filter()->concat($product->images->pluck('url'));
$variantAttributes = $product->variants
    ->flatMap(function ($variant) {
        return $variant->attributeOptions;
    })
    ->groupBy('attribute_id')
    ->map(function ($items) {
        return [
            'attribute' => $items->first()->attribute,
            'options' => $items->unique('id')->values(),
        ];
    })
    ->values();
$defaultVariant = $product->variants->firstWhere('stock', '>', 0) ?: $product->variants->first();
$productDiscount = app(\Modules\Ecommerce\Services\PricingManager::class)->productDiscount($product);
$variantsPayload = $product->variants->map(function ($variant) {
    return [
        'id' => $variant->id,
        'price' => (float) $variant->price,
        'stock' => (int) $variant->stock,
        'allow_backorder' => (bool) $variant->allow_backorder,
        'options' => $variant->attributeOptions->map(function ($option) {
            return [
                'attribute_id' => $option->attribute_id,
                'id' => $option->id,
                'name' => $option->name,
            ];
        })->values(),
    ];
})->values();
@endphp

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('ecommerce.shop.index') }}">Shop</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('ecommerce.shop.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-0">
                    <img id="mainImage" src="{{ $allImages->first() ?: asset('admin/assets/images/no-image-available.jpg') }}" class="img-fluid w-100" alt="{{ $product->name }}" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>
            @if($allImages->count() > 1)
                <div class="row g-2 mt-2">
                    @foreach($allImages as $image)
                        <div class="col-3">
                            <img src="{{ $image }}" class="img-fluid rounded border" alt="{{ $product->name }}" onclick="document.getElementById('mainImage').src=this.src" style="height:80px;width:100%;object-fit:cover;cursor:pointer;">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-6">
            <div class="small text-muted mb-2">
                {{ optional($product->category)->name }}
                @if($product->brand) / {{ $product->brand->name }} @endif
                @if($product->vendor) / {{ $product->vendor->name }} @endif
            </div>
            <h1 class="h3 mb-3">{{ $product->name }}</h1>

            <div class="mb-3">
                @if($productDiscount)
                    <span class="badge bg-danger mb-2">{{ $productDiscount->badge_text }}</span><br>
                @endif
                <span id="productBasePrice" class="h3">
                    {{ number_format((float) (optional($defaultVariant)->price ?: ($productDiscount ? $product->final_price : ($product->sale_price ?: $product->base_price))), 2) }}
                </span>
                @if($productDiscount)
                    <span class="text-decoration-line-through text-muted ms-2">{{ number_format((float) ($product->sale_price ?: $product->base_price), 2) }}</span>
                @elseif($product->sale_price && $product->sale_price < $product->base_price)
                    <span class="text-decoration-line-through text-muted ms-2">{{ number_format((float) $product->base_price, 2) }}</span>
                @endif
            </div>

            @if($product->tags->isNotEmpty())
                <div class="mb-3">
                    @foreach($product->tags as $tag)
                        <a href="{{ route('ecommerce.shop.index', ['tag' => $tag->slug]) }}" class="badge bg-light text-dark text-decoration-none">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            <p class="text-muted mb-4">{{ $product->short_description }}</p>

            <form id="add-to-cart-form" class="row g-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" id="selectedVariantId" value="{{ optional($defaultVariant)->id }}">

                @if($variantAttributes->isNotEmpty())
                    @foreach($variantAttributes as $group)
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ $group['attribute']->name }}</label>
                            <div class="d-flex flex-wrap gap-2 variant-attribute-group" data-attribute-id="{{ $group['attribute']->id }}">
                                @foreach($group['options'] as $option)
                                    <button
                                        type="button"
                                        class="btn btn-sm {{ $group['attribute']->type === 'color' ? 'btn-outline-dark' : 'btn-outline-secondary' }} variant-option"
                                        data-attribute-id="{{ $group['attribute']->id }}"
                                        data-option-id="{{ $option->id }}"
                                    >
                                        @if($group['attribute']->type === 'color')
                                            <span class="d-inline-block rounded-circle me-1 align-middle" style="width:12px;height:12px;background:{{ $option->value ?: '#999999' }};"></span>
                                        @endif
                                        {{ $option->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                <div class="col-sm-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control">
                </div>

                <div class="col-12" id="stockStatus"></div>

                <div class="col-sm-8 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit" id="addToCartBtn">Add To Cart</button>
                </div>
            </form>

            <div id="cart-response" class="small mt-3"></div>

            <hr class="my-4">

            <div>
                <h5>Description</h5>
                <div class="text-muted">{!! nl2br(e($product->description)) !!}</div>
            </div>

            @if($product->tax_percentage || $product->shipping_cost || !empty($product->delivery_rules))
                <hr class="my-4">
                <div class="row g-3">
                    @if($product->tax_percentage)
                        <div class="col-6">
                            <div class="small text-muted">Tax</div>
                            <div>{{ $product->tax_percentage }}%</div>
                        </div>
                    @endif
                    @if($product->shipping_cost)
                        <div class="col-6">
                            <div class="small text-muted">Shipping</div>
                            <div>{{ number_format((float) $product->shipping_cost, 2) }}</div>
                        </div>
                    @endif
                    @if(!empty($product->delivery_rules))
                        <div class="col-12">
                            <div class="small text-muted mb-1">Delivery Rules</div>
                            <ul class="mb-0 ps-3">
                                @foreach($product->delivery_rules as $rule)
                                    <li>{{ $rule }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <div class="mt-5">
            <h4 class="mb-3">Related Products</h4>
            <div class="row g-4">
                @foreach($relatedProducts as $related)
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img src="{{ $related->featured_image_url ?: asset('admin/assets/images/no-image-available.jpg') }}" class="card-img-top" alt="{{ $related->name }}" style="height:150px;object-fit:cover;">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $related->name }}</h6>
                                <strong>{{ number_format((float) ($related->sale_price ?: $related->base_price), 2) }}</strong>
                                <a href="{{ route('ecommerce.products.show', $related->slug) }}" class="btn btn-outline-primary btn-sm w-100 mt-2">View</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const variants = @json($variantsPayload);

    const selected = {};
    const variantIdInput = document.getElementById('selectedVariantId');
    const qtyInput = document.getElementById('quantity');
    const priceEl = document.getElementById('productBasePrice');
    const stockStatus = document.getElementById('stockStatus');
    const addToCartBtn = document.getElementById('addToCartBtn');

    const productStock = @json((int) $product->stock);
    const productBackorder = @json((bool) $product->allow_backorder);
    const productPrice = @json((float) ($product->sale_price ?: $product->base_price));

    let selectedVariant = variants.find(item => item.id === Number(variantIdInput.value)) || null;

    function variantMatchesSelections(variant) {
        return Object.entries(selected).every(([attributeId, optionId]) =>
            variant.options.some(option => option.attribute_id === Number(attributeId) && option.id === Number(optionId))
        );
    }

    function updateState() {
        const match = variants.find(variantMatchesSelections) || selectedVariant || variants[0] || null;
        selectedVariant = match;

        if (variantIdInput) {
            variantIdInput.value = match ? match.id : '';
        }

        document.querySelectorAll('.variant-option').forEach(button => {
            button.classList.toggle('btn-primary', selected[button.dataset.attributeId] === Number(button.dataset.optionId));
        });

        if (!variants.length) {
            if (priceEl) priceEl.textContent = Number(productPrice).toFixed(2);
            updateStockDisplay(productStock, productBackorder);
            return;
        }

        if (!match) {
            if (stockStatus) stockStatus.innerHTML = '<span class="badge bg-secondary">Select a variant</span>';
            if (addToCartBtn) addToCartBtn.disabled = true;
            return;
        }

        if (priceEl) priceEl.textContent = Number(match.price).toFixed(2);
        updateStockDisplay(match.stock, match.allow_backorder);
    }

    function updateStockDisplay(stock, allowBackorder) {
        if (!stockStatus || !addToCartBtn) return;

        if (stock <= 0 && !allowBackorder) {
            stockStatus.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
            addToCartBtn.disabled = true;
        } else if (stock > 0 && stock <= 5) {
            stockStatus.innerHTML = `<span class="badge bg-warning text-dark">Only ${stock} left</span>`;
            addToCartBtn.disabled = false;
            if (qtyInput) qtyInput.max = stock;
        } else if (allowBackorder && stock <= 0) {
            stockStatus.innerHTML = '<span class="badge bg-info text-dark">Available on backorder</span>';
            addToCartBtn.disabled = false;
            if (qtyInput) qtyInput.removeAttribute('max');
        } else {
            stockStatus.innerHTML = '<span class="badge bg-success">In Stock</span>';
            addToCartBtn.disabled = false;
            if (qtyInput) qtyInput.removeAttribute('max');
        }
    }

    document.querySelectorAll('.variant-option').forEach(button => {
        button.addEventListener('click', function () {
            selected[button.dataset.attributeId] = Number(button.dataset.optionId);
            updateState();
        });
    });

    const cartForm = document.getElementById('add-to-cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const originalText = addToCartBtn.textContent;
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Adding...';

            try {
                const response = await fetch(@json(route('ecommerce.cart.store')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(cartForm),
                });

                const result = await response.json();
                const responseContainer = document.getElementById('cart-response');
                if (responseContainer) {
                    responseContainer.innerHTML = `<div class="alert ${result.success ? 'alert-success' : 'alert-danger'} mb-0">${result.message}</div>`;
                }

                if (result.cart_count !== undefined) {
                    const countEl = document.querySelector('[data-cart-count]');
                    if (countEl) countEl.textContent = result.cart_count;
                }
            } catch (error) {
                const responseContainer = document.getElementById('cart-response');
                if (responseContainer) {
                    responseContainer.innerHTML = '<div class="alert alert-danger mb-0">Something went wrong. Please try again.</div>';
                }
            }

            addToCartBtn.textContent = originalText;
            updateState();
        });
    }

    @if(isset($defaultVariant) && $defaultVariant)
        @foreach($defaultVariant->attributeOptions as $option)
            selected[{{ $option->attribute_id }}] = {{ $option->id }};
        @endforeach
    @endif

    updateState();
});
</script>
@endpush
