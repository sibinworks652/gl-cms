<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Ecommerce\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        return view('ecommerce::admin.coupons.index', [
            'coupons' => Coupon::query()->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('ecommerce::admin.coupons.form', [
            'coupon' => new Coupon(['status' => true, 'type' => 'percentage']),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        Coupon::create($this->validated($request));

        return redirect()->route('admin.ecommerce.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        return view('ecommerce::admin.coupons.form', [
            'coupon' => $coupon,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.ecommerce.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.ecommerce.coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    protected function validated(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'boolean'],
        ]) + [
            'code' => strtoupper($request->input('code')),
            'status' => $request->boolean('status'),
        ];
    }
}
