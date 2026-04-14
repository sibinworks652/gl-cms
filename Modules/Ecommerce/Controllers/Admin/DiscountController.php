<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Discount;
use Modules\Ecommerce\Models\Product;

class DiscountController extends Controller
{
    public function index()
    {
        return view('ecommerce::admin.discounts.index', [
            'discounts' => Discount::query()->with(['products', 'categories'])->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('ecommerce::admin.discounts.form', [
            'discount' => new Discount(['status' => true, 'type' => 'percentage']),
            'products' => Product::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $discount = Discount::create($data);
        $discount->products()->sync($request->input('product_ids', []));
        $discount->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.ecommerce.discounts.index')->with('success', 'Discount created successfully.');
    }

    public function edit(Discount $discount)
    {
        $discount->load(['products', 'categories']);

        return view('ecommerce::admin.discounts.form', [
            'discount' => $discount,
            'products' => Product::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Discount $discount)
    {
        $discount->update($this->validated($request));
        $discount->products()->sync($request->input('product_ids', []));
        $discount->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.ecommerce.discounts.index')->with('success', 'Discount updated successfully.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('admin.ecommerce.discounts.index')->with('success', 'Discount deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]) + [
            'status' => $request->boolean('status'),
        ];
    }
}
