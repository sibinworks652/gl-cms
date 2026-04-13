@extends('ecommerce::web.layout')

@section('title', 'Shop')

@section('content')
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="d-grid gap-3">
                            <div>
                                <label class="form-label">Search</label>
                                <input type="search" name="search" class="form-control" value="{{ request('search') }}">
                            </div>
                            <div>
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Vendor</label>
                                <select name="vendor" class="form-select">
                                    <option value="">All vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->slug }}" @selected(request('vendor') === $vendor->slug)>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit">Apply Filters</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row g-4">
                    @forelse($products as $product)
                        <div class="col-md-6 col-xl-4">
                            <div class="card product-card h-100">
                                <img src="{{ $product->featured_image_url ?: asset('admin/assets/images/no-image-available.jpg') }}" class="card-img-top" alt="{{ $product->name }}">
                                <div class="card-body d-flex flex-column">
                                    <div class="small text-muted mb-1">{{ $product->category?->name }} @if($product->vendor) / {{ $product->vendor->name }} @endif</div>
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="text-muted flex-grow-1">{{ \Illuminate\Support\Str::limit($product->short_description ?: strip_tags($product->description), 90) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ number_format((float) $product->sale_price ?: (float) $product->base_price, 2) }}</strong>
                                        <a href="{{ route('ecommerce.products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-light border">No products available yet.</div></div>
                    @endforelse
                </div>
                <div class="mt-4">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
@endsection
