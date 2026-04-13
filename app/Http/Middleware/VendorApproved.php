<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Vendor;
use Symfony\Component\HttpFoundation\Response;

class VendorApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        if ($vendor->isPending()) {
            return redirect()->route('vendor.dashboard');
        }

        if ($vendor->isRejected()) {
            return redirect()->route('vendor.dashboard');
        }

        if (!$vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }

        return $next($request);
    }
}
