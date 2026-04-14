@extends('ecommerce::web.layout')

@section('title', 'Shop')

@php
$sortOptions = [
    '' => 'Default',
    'created_at-desc' => 'Newest First',
    'created_at-asc' => 'Oldest First',
    'base_price-asc' => 'Price: Low to High',
    'base_price-desc' => 'Price: High to Low',
    'name-asc' => 'Name: A-Z',
    'name-desc' => 'Name: Z-A',
];
@endphp

@section('content')
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="d-grid gap-3">
                            <div>
                                <label class="form-label">Search</label>
                                <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search products...">
                            </div>
                            <hr>
                            <div>
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Brand</label>
                                <select name="brand" class="form-select form-select-sm">
                                    <option value="">All brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Vendor</label>
                                <select name="vendor" class="form-select form-select-sm">
                                    <option value="">All vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->slug }}" @selected(request('vendor') === $vendor->slug)>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <hr>
                            <div>
                                <label class="form-label">Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control form-control-sm" value="{{ request('min_price') }}" placeholder="Min">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control form-control-sm" value="{{ request('max_price') }}" placeholder="Max">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-check">
                                <input type="checkbox" name="in_stock" value="1" class="form-check-input" id="inStock" @checked(request('in_stock'))>
                                <label class="form-check-label" for="inStock">In Stock Only</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="on_sale" value="1" class="form-check-input" id="onSale" @checked(request('on_sale'))>
                                <label class="form-check-label" for="onSale">On Sale</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" @checked(request('featured'))>
                                <label class="form-check-label" for="featured">Featured</label>
                            </div>
                            <hr>
                            <div>
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select form-select-sm">
                                    @foreach($sortOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(request('sort') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm" type="submit">Apply Filters</button>
                                <a href="{{ route('ecommerce.shop.index') }}" class="btn btn-outline-secondary btn-sm">Clear All</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                @if($tags->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Tags</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <a href="{{ route('ecommerce.shop.index', ['tag' => $tag->slug]) }}" class="badge bg-light text-dark {{ request('tag') === $tag->slug ? 'bg-primary text-white' : '' }}">
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</div>
                </div>
                
                <div class="row g-4">
                    @forelse($products as $product)
                        <div class="col-md-6 col-xl-4">
                            <div class="card product-card h-100">
                                <div class="position-relative">
                                    <img src="{{ $product->featured_image_url ?: asset('admin/assets/images/no-image-available.jpg') }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                    @if($product->is_featured)
                                    <span class="position-absolute top-0 end-0 badge bg-warning m-2">Featured</span>
                                    @endif
                                    @php($activeDiscount = app(\Modules\Ecommerce\Services\PricingManager::class)->productDiscount($product))
                                    @if($activeDiscount)
                                    <span class="position-absolute top-0 start-0 badge bg-danger m-2">{{ $activeDiscount->badge_text }}</span>
                                    @elseif($product->sale_price && $product->sale_price < $product->base_price)
                                    <span class="position-absolute top-0 start-0 badge bg-danger m-2">Sale</span>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="small text-muted mb-1">
                                        {{ $product->category?->name }}
                                        @if($product->brand)
                                         / {{ $product->brand->name }}
                                        @endif
                                    </div>
                                    <h6 class="card-title mb-2">{{ $product->name }}</h6>
                                    <p class="text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit($product->short_description ?: strip_tags($product->description), 80) }}</p>
                                    
                                    @if($product->tags->isNotEmpty())
                                    <div class="mb-2">
                                        @foreach($product->tags->take(3) as $tag)
                                        <span class="badge bg-light text-dark small">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div>
                                            @if($activeDiscount)
                                            <span class="text-decoration-line-through text-muted small">{{ number_format((float) ($product->sale_price ?: $product->base_price), 2) }}</span>
                                            <strong class="text-danger">{{ number_format((float) $product->final_price, 2) }}</strong>
                                            @elseif($product->sale_price && $product->sale_price < $product->base_price)
                                            <span class="text-decoration-line-through text-muted small">{{ number_format((float) $product->base_price, 2) }}</span>
                                            <strong class="text-danger">{{ number_format((float) $product->sale_price, 2) }}</strong>
                                            @else
                                            <strong>{{ number_format((float) $product->base_price, 2) }}</strong>
                                            @endif
                                        </div>
                                        <a href="{{ route('ecommerce.products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light border text-center py-5">
                                <h5>No products found</h5>
                                <p class="mb-0">Try adjusting your filters or search criteria.</p>
                                <a href="{{ route('ecommerce.shop.index') }}" class="btn btn-primary mt-2">Clear Filters</a>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                @if($products->hasPages())
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
