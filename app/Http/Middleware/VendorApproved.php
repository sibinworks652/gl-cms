<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Vendor;
use Modules\Settings\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class VendorApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $moduleEnabled = in_array((string) Setting::value('ecommerce_vendor_module_enabled', '1'), ['1', 'true', 'on', 'yes'], true);

        abort_unless($moduleEnabled, 404, 'Vendor module is currently disabled.');

        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        if ($vendor->isPending()) {
            $request->session()->put('vendor_id', $vendor->id);
            return redirect()->route('vendor.pending');
        }

        if ($vendor->isRejected()) {
            $request->session()->put('vendor_id', $vendor->id);
            return redirect()->route('vendor.pending');
        }

        if (!$vendor->isApproved()) {
            $request->session()->put('vendor_id', $vendor->id);
            return redirect()->route('vendor.pending');
        }

        return $next($request);
    }
}
