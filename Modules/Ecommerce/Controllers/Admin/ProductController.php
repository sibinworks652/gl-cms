<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Requests\ProductRequest;
use Modules\Ecommerce\Services\CatalogManager;

class ProductController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function index(Request $request)
    {
        return view('ecommerce::admin.products.index', [
            'products' => $this->catalog->adminProducts($request->only(['search', 'category_id', 'status'])),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('ecommerce::admin.products.form', [
            'product' => new Product(['status' => true]),
            'categories' => Category::query()->ordered()->get(),
            'vendors' => Vendor::query()->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $this->catalog->saveProduct(
            $request->validated(),
            null,
            $request->file('featured_image'),
            $request->file('gallery_images', [])
        );

        return redirect()->route('admin.ecommerce.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants', 'images']);

        return view('ecommerce::admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(),
            'vendors' => Vendor::query()->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->catalog->saveProduct(
            $request->validated(),
            $product,
            $request->file('featured_image'),
            $request->file('gallery_images', [])
        );

        return redirect()->route('admin.ecommerce.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->catalog->deleteProduct($product->load('images'));

        return redirect()->route('admin.ecommerce.products.index')->with('success', 'Product deleted successfully.');
    }
}
