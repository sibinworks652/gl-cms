<?php

namespace Modules\Faq\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Faq\Models\FaqCategory;
use Modules\Faq\Requests\FaqCategoryRequest;
use Modules\Faq\Services\FaqManager;

class FaqCategoryController extends Controller
{
    public function __construct(protected FaqManager $manager)
    {
    }

    public function index()
    {
        return view('faq::admin.categories.index', [
            'categories' => FaqCategory::query()->withCount('faqs')->ordered()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('faq::admin.categories.form', [
            'category' => new FaqCategory([
                'status' => true,
                'order' => (int) FaqCategory::max('order') + 1,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(FaqCategoryRequest $request)
    {
        $category = $this->manager->createCategory($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'FAQ category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ], 201);
        }

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category created successfully.');
    }

    public function edit(FaqCategory $faq_category)
    {
        return view('faq::admin.categories.form', [
            'category' => $faq_category,
            'isEdit' => true,
        ]);
    }

    public function update(FaqCategoryRequest $request, FaqCategory $faq_category)
    {
        $this->manager->updateCategory($faq_category, $request->validated());

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category updated successfully.');
    }

    public function destroy(FaqCategory $faq_category)
    {
        $faq_category->delete();

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('faq_categories', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                FaqCategory::query()->whereKey($id)->update(['order' => $index]);
            }
        });

        return response()->json(['message' => 'Category order saved.']);
    }
}
