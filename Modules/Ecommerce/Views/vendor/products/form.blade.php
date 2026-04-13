<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isEdit ? 'Edit' : 'Create' }} Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('vendor.dashboard') }}">Vendor Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('vendor.dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('vendor.products.index') }}">Products</a>
                <a class="nav-link" href="{{ route('vendor.orders.index') }}">Orders</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2>{{ $isEdit ? 'Edit' : 'Create' }} Product</h2>

        <form method="POST" action="{{ $isEdit ? route('vendor.products.update', $product) : route('vendor.products.store') }}" 
              enctype="multipart/form-data" class="row g-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h5>Basic Information</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @endif" 
                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU *</label>
                                <input type="text" class="form-control @error('sku') is-invalid @endif" 
                                       id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select @error('category_id') is-invalid @endif" 
                                        id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea class="form-control @error('short_description') is-invalid @endif" 
                                      id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @endif" 
                                      id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5>Pricing & Stock</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="base_price" class="form-label">Base Price *</label>
                            <input type="number" class="form-control @error('base_price') is-invalid @endif" 
                                   id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" 
                                   step="0.01" min="0" required>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Sale Price</label>
                            <input type="number" class="form-control @error('sale_price') is-invalid @endif" 
                                   id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" 
                                   step="0.01" min="0">
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control @error('stock') is-invalid @endif" 
                                   id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5>Images</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control @error('featured_image') is-invalid @endif" 
                                   id="featured_image" name="featured_image" accept="image/*">
                            @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                            @if($product->featured_image)
                                <div class="mt-2">
                                    <img src="{{ $product->featured_image_url }}" alt="Featured" style="max-width: 100px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5>Status</h5></div>
                    <div class="card-body">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" 
                                   {{ old('status', $product->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" 
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }} Product</button>
                <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
