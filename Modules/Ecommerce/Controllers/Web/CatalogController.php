<?php

namespace Modules\Ecommerce\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Services\CatalogManager;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function index(Request $request)
    {
        return view('ecommerce::web.shop', [
            'products' => $this->catalog->storefrontProducts($request->only(['search', 'category', 'vendor', 'featured'])),
            'categories' => $this->catalog->activeCategories(),
            'vendors' => $this->catalog->activeVendors(),
        ]);
    }

    public function show(string $slug)
    {
        return view('ecommerce::web.product', [
            'product' => $this->catalog->findProductBySlug($slug),
        ]);
    }
}
