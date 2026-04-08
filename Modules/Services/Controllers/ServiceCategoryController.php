<?php

namespace Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Requests\ServiceCategoryRequest;
use Modules\Services\Services\ServiceManager;

class ServiceCategoryController extends Controller
{
    public function __construct(protected ServiceManager $manager)
    {
    }

    public function index()
    {
        return view('services::admin.categories.index', [
            'categories' => ServiceCategory::query()->withCount('services')->ordered()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('services::admin.categories.form', [
            'category' => new ServiceCategory(['is_active' => true, 'sort_order' => (int) ServiceCategory::max('sort_order') + 1]),
            'isEdit' => false,
        ]);
    }

    public function store(ServiceCategoryRequest $request)
    {
        $category = $this->manager->createCategory($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Service category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ], 201);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category created successfully.');
    }

    public function edit(ServiceCategory $category)
    {
        return view('services::admin.categories.form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    public function update(ServiceCategoryRequest $request, ServiceCategory $category)
    {
        $this->manager->updateCategory($category, $request->validated());

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category updated successfully.');
    }

    public function destroy(ServiceCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('service_categories', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                ServiceCategory::query()->whereKey($id)->update(['sort_order' => $index]);
            }
        });

        return response()->json([
            'message' => 'Category order saved.',
        ]);
    }
}
