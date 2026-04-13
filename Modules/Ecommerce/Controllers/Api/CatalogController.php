<?php

namespace Modules\Ecommerce\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Resources\CategoryResource;
use Modules\Ecommerce\Resources\ProductResource;
use Modules\Ecommerce\Services\CatalogManager;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()->active()->with('children')->ordered()->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = $this->catalog->storefrontProducts($request->only(['search', 'category', 'vendor', 'featured']));

        return response()->json([
            'data' => ProductResource::collection($products->getCollection()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function product(string $slug): JsonResponse
    {
        return response()->json([
            'data' => new ProductResource($this->catalog->findProductBySlug($slug)),
        ]);
    }
}
