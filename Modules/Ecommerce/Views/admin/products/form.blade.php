@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.ecommerce.products.update', $product) : route('admin.ecommerce.products.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Product' : 'Create Product' }}</h4>
                    <p class="text-muted mb-0">Add product content, pricing, inventory, images, and variants.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.ecommerce.products.index') }}" class="btn btn-light">Back</a>
                    <button class="btn btn-primary" type="submit">Save Product</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input name="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vendor</label>
                                <select name="vendor_id" class="form-select">
                                    <option value="">Marketplace</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $product->vendor_id) === (string) $vendor->id)>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Base Price</label>
                                <input name="base_price" type="number" step="0.01" class="form-control" value="{{ old('base_price', $product->base_price) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sale Price</label>
                                <input name="sale_price" type="number" step="0.01" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock</label>
                                <input name="stock" type="number" class="form-control" value="{{ old('stock', $product->stock) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Featured Image</label>
                                <input name="featured_image" type="file" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gallery Images</label>
                                <input name="gallery_images[]" type="file" class="form-control" multiple>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" rows="3" class="form-control">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h5 class="mb-0">Variants</h5></div>
                        <div class="card-body">
                            @php($variants = old('variants', $product->variants->map->only(['id', 'sku', 'size', 'color', 'price', 'stock', 'status'])->all() ?: [['sku' => '', 'size' => '', 'color' => '', 'price' => '', 'stock' => '', 'status' => 1]]))
                            @foreach($variants as $index => $variant)
                                <div class="row g-2 border rounded p-3 mb-3">
                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] ?? '' }}">
                                    <div class="col-md-3"><input name="variants[{{ $index }}][sku]" class="form-control" placeholder="Variant SKU" value="{{ $variant['sku'] ?? '' }}"></div>
                                    <div class="col-md-2"><input name="variants[{{ $index }}][size]" class="form-control" placeholder="Size" value="{{ $variant['size'] ?? '' }}"></div>
                                    <div class="col-md-2"><input name="variants[{{ $index }}][color]" class="form-control" placeholder="Color" value="{{ $variant['color'] ?? '' }}"></div>
                                    <div class="col-md-2"><input name="variants[{{ $index }}][price]" type="number" step="0.01" class="form-control" placeholder="Price" value="{{ $variant['price'] ?? '' }}"></div>
                                    <div class="col-md-2"><input name="variants[{{ $index }}][stock]" type="number" class="form-control" placeholder="Stock" value="{{ $variant['stock'] ?? '' }}"></div>
                                    <div class="col-md-1">
                                        <select name="variants[{{ $index }}][status]" class="form-select">
                                            <option value="1" @selected(($variant['status'] ?? 1) == 1)>On</option>
                                            <option value="0" @selected(($variant['status'] ?? 1) == 0)>Off</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                            <p class="small text-muted mb-0">Add more variant rows by duplicating this block as needed. The service supports multiple variant records.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
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
                                <label class="form-check-label" for="featured">Featured product</label>
                            </div>
                        </div>
                    </div>

                    @if($isEdit && $product->images->isNotEmpty())
                        <div class="card mt-4">
                            <div class="card-header"><h5 class="mb-0">Gallery</h5></div>
                            <div class="card-body row g-2">
                                @foreach($product->images as $image)
                                    <div class="col-6">
                                        <img src="{{ $image->url }}" alt="" class="img-fluid rounded">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
