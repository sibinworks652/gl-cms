<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Product;
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
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $products = $vendor->products()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->input('search') . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::vendor.products.index', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }

    public function create()
    {
        $user = request()->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        return view('ecommerce::vendor.products.form', [
            'product' => new Product(['status' => true]),
            'categories' => Category::query()->ordered()->get(),
            'vendor' => $vendor,
            'isEdit' => false,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $data = $request->validated();
        $data['vendor_id'] = $vendor->id;

        $this->catalog->saveProduct(
            $data,
            null,
            $request->file('featured_image'),
            $request->file('gallery_images', [])
        );

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $user = request()->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'You can only edit your own products.');
        }

        $product->load(['variants', 'images']);

        return view('ecommerce::vendor.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(),
            'vendor' => $vendor,
            'isEdit' => true,
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'You can only edit your own products.');
        }

        $this->catalog->saveProduct(
            $request->validated(),
            $product,
            $request->file('featured_image'),
            $request->file('gallery_images', [])
        );

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $user = request()->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'You can only delete your own products.');
        }

        $this->catalog->deleteProduct($product->load('images'));

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
    }
}
