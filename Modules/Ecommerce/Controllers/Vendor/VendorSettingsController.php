<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Services\VendorService;

class VendorSettingsController extends Controller
{
    public function __construct(
        protected VendorService $vendorService,
    ) {
    }

    public function profile()
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());
        return view('ecommerce::vendor.settings.profile', ['vendor' => $vendor]);
    }

    public function updateProfile(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email,' . $vendor->id,
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $data['logo'] = $request->file('logo')->store('vendors', 'public');
        }

        $this->vendorService->updateVendorProfile($vendor, $data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function storeSettings()
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());
        return view('ecommerce::vendor.settings.store', ['vendor' => $vendor]);
    }

    public function updateStoreSettings(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        if (isset($data['name']) && $data['name'] !== $vendor->name) {
            $data['slug'] = $this->generateUniqueSlug($vendor, $data['name']);
        }

        $vendor->update($data);

        return back()->with('success', 'Store settings updated.');
    }

    public function bankDetails()
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());
        return view('ecommerce::vendor.settings.bank', ['vendor' => $vendor]);
    }

    public function updateBankDetails(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $data = $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'paypal_email' => 'nullable|email|max:255',
        ]);

        $vendor->update($data);

        return back()->with('success', 'Bank details updated.');
    }

    public function notifications()
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());
        return view('ecommerce::vendor.settings.notifications', ['vendor' => $vendor]);
    }

    protected function generateUniqueSlug(Vendor $vendor, string $name): string
    {
        $slug = Str::slug($name);
        $exists = Vendor::where('slug', $slug)->whereKeyNot($vendor->id)->exists();
        
        if ($exists) {
            $counter = 2;
            while (Vendor::where('slug', $slug . '-' . $counter)->whereKeyNot($vendor->id)->exists()) {
                $counter++;
            }
            return $slug . '-' . $counter;
        }
        
        return $slug;
    }
}