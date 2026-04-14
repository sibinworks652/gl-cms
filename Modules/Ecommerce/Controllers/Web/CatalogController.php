<?php

namespace Modules\Ecommerce\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Brand;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Tag;
use Modules\Ecommerce\Services\CatalogManager;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function index(Request $request)
    {
        [$sort, $sortDirection] = $this->parseSort($request->string('sort')->toString());

        return view('ecommerce::web.shop', [
            'products' => $this->catalog->storefrontProducts($request->only([
                'search', 'category', 'category_id', 'vendor', 'brand', 'brand_id', 'tag',
                'min_price', 'max_price', 'featured', 'in_stock', 'on_sale'
            ]) + [
                'sort' => $sort,
                'sort_dir' => $sortDirection,
            ]),
            'categories' => $this->catalog->activeCategories(),
            'vendors' => $this->catalog->activeVendors(),
            'brands' => Brand::query()->where('status', true)->orderBy('name')->get(),
            'tags' => Tag::query()->where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $product = $this->catalog->findProductBySlug($slug);
        $product->load(['images', 'variants.attributeOptions.attribute', 'tags', 'attributes.options', 'attributeOptions']);

        return view('ecommerce::web.product', [
            'product' => $product,
            'relatedProducts' => $this->catalog->storefrontProducts([
                'category_id' => $product->category_id,
                'featured' => false
            ], 4)
        ]);
    }

    protected function parseSort(string $sort): array
    {
        $allowed = ['created_at', 'base_price', 'name'];

        if (! str_contains($sort, '-')) {
            return ['created_at', 'desc'];
        }

        [$column, $direction] = explode('-', $sort, 2);

        return [
            in_array($column, $allowed, true) ? $column : 'created_at',
            $direction === 'asc' ? 'asc' : 'desc',
        ];
    }
}
