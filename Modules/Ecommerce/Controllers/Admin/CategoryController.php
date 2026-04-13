<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Requests\CategoryRequest;
use Modules\Ecommerce\Services\CatalogManager;

class CategoryController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function index(Request $request)
    {
        $categories = Category::query()
            ->with('parent')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->boolean('status')))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('ecommerce::admin.categories.form', [
            'category' => new Category(['status' => true]),
            'parents' => Category::query()->ordered()->get(),
            'isEdit' => false,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $this->catalog->createCategory($request->validated(), $request->file('image'));

        return redirect()->route('admin.ecommerce.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('ecommerce::admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->whereKeyNot($category->id)->ordered()->get(),
            'isEdit' => true,
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->catalog->updateCategory($category, $request->validated(), $request->file('image'));

        return redirect()->route('admin.ecommerce.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.ecommerce.categories.index')->with('success', 'Category deleted successfully.');
    }
}
