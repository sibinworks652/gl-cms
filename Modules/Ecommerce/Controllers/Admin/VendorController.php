<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Requests\VendorRequest;
use Modules\Ecommerce\Services\CatalogManager;

class VendorController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function index(Request $request)
    {
        $vendors = Vendor::query()
            ->withCount('products')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::admin.vendors.index', [
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        return view('ecommerce::admin.vendors.form', [
            'vendor' => new Vendor(['status' => 'approved']),
            'users' => User::query()->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(VendorRequest $request)
    {
        $this->catalog->createVendor($request->validated(), $request->file('logo'));

        return redirect()->route('admin.ecommerce.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('ecommerce::admin.vendors.form', [
            'vendor' => $vendor,
            'users' => User::query()->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(VendorRequest $request, Vendor $vendor)
    {
        $this->catalog->updateVendor($vendor, $request->validated(), $request->file('logo'));

        return redirect()->route('admin.ecommerce.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->user) {
            $vendor->user->removeRole('vendor');
        }
        $vendor->delete();

        return redirect()->route('admin.ecommerce.vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function approve(Vendor $vendor)
    {
        $this->catalog->approveVendor($vendor);

        return redirect()->route('admin.ecommerce.vendors.index')->with('success', 'Vendor approved successfully.');
    }

    public function reject(Request $request, Vendor $vendor)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->catalog->rejectVendor($vendor, $request->input('reason'));

        return redirect()->route('admin.ecommerce.vendors.index')->with('success', 'Vendor rejected.');
    }
}
