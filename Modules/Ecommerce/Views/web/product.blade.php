@extends('ecommerce::web.layout')

@section('title', $product->name)

@section('content')
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <img src="{{ $product->featured_image_url ?: asset('admin/assets/images/no-image-available.jpg') }}" class="img-fluid rounded border mb-3" alt="{{ $product->name }}">
                <div class="row g-2">
                    @foreach($product->images as $image)
                        <div class="col-4"><img src="{{ $image->url }}" class="img-fluid rounded border" alt=""></div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="small text-muted mb-2">{{ $product->category?->name }} @if($product->vendor) / {{ $product->vendor->name }} @endif</div>
                <h1 class="h3">{{ $product->name }}</h1>
                <div class="h4 mb-3">{{ number_format((float) $product->sale_price ?: (float) $product->base_price, 2) }}</div>
                <p class="text-muted">{{ $product->short_description }}</p>
                <div class="mb-4">{!! nl2br(e($product->description)) !!}</div>

                <form id="add-to-cart-form" class="row g-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($product->variants->isNotEmpty())
                        <div class="col-12">
                            <label class="form-label">Variant</label>
                            <select name="product_variant_id" class="form-select">
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->label }} - {{ number_format((float) $variant->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-sm-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" class="form-control">
                    </div>
                    <div class="col-sm-8 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">Add To Cart</button>
                    </div>
                </form>
                <div id="cart-response" class="small mt-3"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('add-to-cart-form')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const response = await fetch(@json(route('ecommerce.cart.store')), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: new FormData(form),
    });
    const result = await response.json();
    const message = document.getElementById('cart-response');
    message.textContent = result.message || 'Added to cart.';
    message.className = 'small mt-3 text-success';
});
</script>
@endpush
